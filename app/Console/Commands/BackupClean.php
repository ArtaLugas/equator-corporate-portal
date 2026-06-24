<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupManager;
use Illuminate\Console\Command;

class BackupClean extends Command
{
    protected $signature = 'backup:clean';

    protected $description = 'Prune old backups beyond the per-tier retention (daily/weekly/monthly).';

    public function handle(BackupManager $manager): int
    {
        $deleted = $manager->clean();

        $this->info("Pruned {$deleted} backup(s) beyond retention.");

        return self::SUCCESS;
    }
}
