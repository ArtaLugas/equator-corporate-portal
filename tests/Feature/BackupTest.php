<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    private string $backupPath = 'backup-test';

    private string $srcDir;

    protected function setUp(): void
    {
        parent::setUp();

        config(['backup.path' => $this->backupPath]);

        // A small, isolated "uploads" dir under the public root.
        $this->srcDir = storage_path('app/public/__backup_test__');
        File::ensureDirectoryExists($this->srcDir);
        File::put($this->srcDir.'/sample.txt', 'hello backup');

        config([
            'backup.include' => [$this->srcDir],
            'backup.exclude' => [],
            'backup.offsite_disk' => null,
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/'.$this->backupPath));
        File::deleteDirectory($this->srcDir);
        parent::tearDown();
    }

    private function base(): string
    {
        return storage_path('app/'.$this->backupPath);
    }

    public function test_daily_backup_creates_restorable_archive(): void
    {
        $this->artisan('backup:run', ['--type' => 'daily'])->assertSuccessful();

        $zips = glob($this->base().'/daily/backup-daily-*.zip');
        $this->assertCount(1, $zips, 'one daily archive');

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($zips[0]) === true);

        // Restorable bundle: db dump + .env + manifest.
        $db = $zip->statName('database.sql.gz');
        $this->assertNotFalse($db);
        $this->assertGreaterThan(0, $db['size'], 'db dump non-empty');
        $this->assertNotFalse($zip->statName('env'));

        $manifest = json_decode($zip->getFromName('manifest.json'), true);
        $this->assertSame('daily', $manifest['type']);
        $this->assertFalse($manifest['includes_files']);  // daily = DB only by default

        // The dump is real SQL.
        $sql = gzdecode($zip->getFromName('database.sql.gz'));
        $this->assertStringContainsString('CREATE TABLE', $sql);
        $zip->close();

        // Integrity sidecar written.
        $this->assertFileExists($zips[0].'.sha256');
    }

    public function test_weekly_backup_includes_uploaded_files(): void
    {
        $this->artisan('backup:run', ['--type' => 'weekly'])->assertSuccessful();

        $zips = glob($this->base().'/weekly/backup-weekly-*.zip');
        $zip = new ZipArchive;
        $zip->open($zips[0]);

        $this->assertNotFalse($zip->statName('files/__backup_test__/sample.txt'), 'uploaded file archived');

        $manifest = json_decode($zip->getFromName('manifest.json'), true);
        $this->assertTrue($manifest['includes_files']);
        $this->assertGreaterThanOrEqual(1, $manifest['file_count']);
        $zip->close();
    }

    public function test_verify_passes_after_backup_and_fails_without(): void
    {
        $this->artisan('backup:verify')->assertFailed(); // nothing yet

        $this->artisan('backup:run', ['--type' => 'daily'])->assertSuccessful();

        $this->artisan('backup:verify')->assertSuccessful();
    }

    public function test_retention_prunes_old_backups(): void
    {
        $dir = $this->base().'/daily';
        File::ensureDirectoryExists($dir);
        foreach (['2026-01-01-000000', '2026-01-02-000000', '2026-01-03-000000', '2026-01-04-000000', '2026-01-05-000000'] as $stamp) {
            File::put($dir."/backup-daily-{$stamp}.zip", 'x');
            File::put($dir."/backup-daily-{$stamp}.zip.sha256", 'x');
        }

        config(['backup.retention.daily' => 2]);

        $this->artisan('backup:clean')->assertSuccessful();

        $remaining = glob($dir.'/backup-daily-*.zip');
        $this->assertCount(2, $remaining);
        // The two newest (lexically largest) survive.
        $this->assertStringContainsString('2026-01-05', implode(' ', $remaining));
        $this->assertStringContainsString('2026-01-04', implode(' ', $remaining));
    }

    public function test_invalid_type_is_rejected(): void
    {
        $this->artisan('backup:run', ['--type' => 'bogus'])->assertExitCode(2);
    }
}
