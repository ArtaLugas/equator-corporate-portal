<?php

namespace App\Services\Backup;

use FilesystemIterator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

/**
 * Orchestrates dependency-free backups: build a single restorable zip per run,
 * optionally copy it off-site, enforce retention, and verify integrity.
 */
class BackupManager
{
    public const TYPES = ['daily', 'weekly', 'monthly'];

    /** Create a backup of the given tier; returns its metadata. */
    public function create(string $type): array
    {
        if (! in_array($type, self::TYPES, true)) {
            throw new RuntimeException("Unknown backup type: {$type}");
        }

        $includeFiles = in_array($type, ['weekly', 'monthly'], true)
            || ($type === 'daily' && config('backup.daily_includes_files'));

        $dir = $this->localBase().'/'.$type;
        File::ensureDirectoryExists($dir);

        $name = "backup-{$type}-".now()->format('Y-m-d-His').'.zip';
        $zipPath = $dir.'/'.$name;

        // 1) Database → temporary gzip.
        $dbGz = tempnam(sys_get_temp_dir(), 'eqdb');
        $dbStrategy = (new DatabaseDumper(config('backup.database')))->dump($dbGz);

        // 2) Assemble the zip.
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($dbGz);
            throw new RuntimeException("Cannot create backup archive: {$zipPath}");
        }

        $zip->addFile($dbGz, 'database.sql.gz');

        if (config('backup.include_env') && File::exists(base_path('.env'))) {
            $zip->addFile(base_path('.env'), 'env');
        }

        $fileCount = 0;
        $fileBytes = 0;
        if ($includeFiles) {
            foreach ($this->iterateFiles() as $abs => $rel) {
                if ($zip->addFile($abs, 'files/'.$rel)) {
                    $fileCount++;
                    $fileBytes += (int) @filesize($abs);
                }
            }
        }

        $manifest = [
            'type' => $type,
            'created_at' => now()->toIso8601String(),
            'app' => config('app.name'),
            'app_url' => config('app.url'),
            'php' => PHP_VERSION,
            'database' => config('database.connections.'.config('backup.database').'.database'),
            'db_strategy' => $dbStrategy,
            'includes_files' => $includeFiles,
            'file_count' => $fileCount,
            'file_bytes' => $fileBytes,
            'restore_target' => 'files/* → storage/app/public/',
        ];
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $zip->close();
        @unlink($dbGz);

        // 3) Checksum sidecar (integrity / verification).
        $sha = hash_file('sha256', $zipPath);
        File::put($zipPath.'.sha256', $sha.'  '.$name."\n");

        // 4) Off-site copy (disaster recovery).
        $offsite = $this->copyOffsite($type, $zipPath, $name);

        return array_merge($manifest, [
            'name' => $name,
            'path' => $zipPath,
            'bytes' => (int) filesize($zipPath),
            'sha256' => $sha,
            'offsite' => $offsite,
        ]);
    }

    /** Enforce per-tier retention. Returns the number of archives deleted. */
    public function clean(): int
    {
        $deleted = 0;

        foreach (self::TYPES as $type) {
            $keep = (int) config("backup.retention.{$type}", 7);
            $zips = $this->localList($type); // newest first

            foreach (array_slice($zips, $keep) as $old) {
                File::delete($old);
                File::delete($old.'.sha256');
                $deleted++;

                if ($disk = config('backup.offsite_disk')) {
                    $remote = config('backup.offsite_path')."/{$type}/".basename($old);
                    try {
                        Storage::disk($disk)->delete([$remote, $remote.'.sha256']);
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            }
        }

        return $deleted;
    }

    /** Verify the newest backup is fresh and structurally sound. */
    public function verify(): array
    {
        $checks = [];
        $newest = $this->newest();

        $checks['a backup exists'] = $newest !== null;
        if ($newest === null) {
            return ['ok' => false, 'backup' => null, 'checks' => $checks];
        }

        $ageHours = (now()->getTimestamp() - filemtime($newest)) / 3600;
        $checks['fresh (< '.config('backup.max_age_hours').'h)'] = $ageHours <= (float) config('backup.max_age_hours', 26);

        $zip = new ZipArchive;
        $opened = $zip->open($newest) === true;
        $checks['archive opens'] = $opened;

        if ($opened) {
            $db = $zip->statName('database.sql.gz');
            $checks['database dump present & non-empty'] = $db !== false && ($db['size'] ?? 0) > 0;
            $checks['manifest present'] = $zip->statName('manifest.json') !== false;
            $zip->close();
        }

        // Checksum matches the sidecar (no silent corruption).
        if (File::exists($newest.'.sha256')) {
            $expected = trim(explode(' ', File::get($newest.'.sha256'))[0]);
            $checks['checksum matches'] = hash_equals($expected, hash_file('sha256', $newest));
        }

        return [
            'ok' => ! in_array(false, $checks, true),
            'backup' => basename($newest),
            'age_hours' => round($ageHours, 1),
            'checks' => $checks,
        ];
    }

    /** All backups newest-first across every tier. */
    public function newest(): ?string
    {
        $all = [];
        foreach (self::TYPES as $type) {
            $all = array_merge($all, $this->localList($type));
        }
        usort($all, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        return $all[0] ?? null;
    }

    private function localBase(): string
    {
        return storage_path('app/'.config('backup.path', 'backups'));
    }

    /** Zip files for a tier, newest-first (timestamped names sort chronologically). */
    private function localList(string $type): array
    {
        $dir = $this->localBase().'/'.$type;
        if (! File::isDirectory($dir)) {
            return [];
        }
        $zips = glob($dir.'/backup-*.zip') ?: [];
        rsort($zips); // lexical desc == newest first

        return $zips;
    }

    /** Yield [absolutePath => pathRelativeToPublicRoot] for the configured includes. */
    private function iterateFiles(): \Generator
    {
        $excludes = array_map(fn ($p) => rtrim($p, '/\\'), config('backup.exclude', []));
        $publicRoot = rtrim(storage_path('app/public'), '/\\');

        foreach (config('backup.include', []) as $base) {
            if (! File::isDirectory($base)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                $abs = $file->getPathname();

                foreach ($excludes as $ex) {
                    if (str_starts_with($abs, $ex)) {
                        continue 2;
                    }
                }

                $rel = ltrim(str_replace($publicRoot, '', $abs), '/\\');
                yield $abs => str_replace('\\', '/', $rel);
            }
        }
    }

    private function copyOffsite(string $type, string $zipPath, string $name): bool
    {
        $disk = config('backup.offsite_disk');
        if (! $disk) {
            return false;
        }

        try {
            $remote = config('backup.offsite_path')."/{$type}/{$name}";
            $stream = fopen($zipPath, 'rb');
            Storage::disk($disk)->writeStream($remote, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
            Storage::disk($disk)->put($remote.'.sha256', File::get($zipPath.'.sha256'));

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
