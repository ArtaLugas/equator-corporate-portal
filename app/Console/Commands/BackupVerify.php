<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupManager;
use Illuminate\Console\Command;

class BackupVerify extends Command
{
    protected $signature = 'backup:verify';

    protected $description = 'Verify the newest backup is fresh and structurally sound (exit non-zero on failure).';

    public function handle(BackupManager $manager): int
    {
        $result = $manager->verify();

        foreach ($result['checks'] as $label => $ok) {
            $this->line(($ok ? '<info>  ✓</info>' : '<error>  ✗</error>')." {$label}");
        }

        if (! $result['ok']) {
            $this->error('Backup verification FAILED'.($result['backup'] ? " — {$result['backup']}" : ' — no backup found'));

            return self::FAILURE;
        }

        $this->info("Backup OK: {$result['backup']} ({$result['age_hours']}h old)");

        return self::SUCCESS;
    }
}
