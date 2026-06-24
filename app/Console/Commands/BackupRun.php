<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupManager;
use Illuminate\Console\Command;

class BackupRun extends Command
{
    protected $signature = 'backup:run {--type=daily : daily | weekly | monthly}';

    protected $description = 'Create a backup (database always; uploaded files for weekly/monthly) + .env.';

    public function handle(BackupManager $manager): int
    {
        $type = (string) $this->option('type');

        if (! in_array($type, BackupManager::TYPES, true)) {
            $this->error('Invalid --type. Use one of: '.implode(', ', BackupManager::TYPES));

            return self::INVALID;
        }

        $this->info("Starting {$type} backup…");

        try {
            $r = $manager->create($type);
        } catch (\Throwable $e) {
            report($e);
            $this->error('Backup failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'OK  %s  (%s, db:%s, files:%d, sha:%s)%s',
            $r['name'],
            $this->human($r['bytes']),
            $r['db_strategy'],
            $r['file_count'],
            substr($r['sha256'], 0, 12),
            $r['offsite'] ? '  [off-site ✓]' : ''
        ));

        return self::SUCCESS;
    }

    private function human(int $bytes): string
    {
        $u = ['B', 'KB', 'MB', 'GB'];
        $i = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $i = min($i, count($u) - 1);

        return round($bytes / (1024 ** $i), 2).' '.$u[$i];
    }
}
