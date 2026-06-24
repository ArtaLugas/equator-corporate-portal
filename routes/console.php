<?php

use App\Models\Visitor;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
| Requires a single system cron entry on the server:
|   * * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
*/

// Prune visitor-analytics rows older than Visitor::RETENTION_DAYS so the
// table stays bounded and analytics queries remain fast as traffic grows.
Schedule::command('model:prune', ['--model' => [Visitor::class]])
    ->daily();

// Process queued jobs (transactional email, notifications) WITHOUT a long-running
// daemon — shared hosting (cPanel) does not allow Supervisor/systemd workers.
// Driven by the single cPanel cron entry that runs `schedule:run` every minute.
Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Backups (database daily; uploaded files weekly + monthly) — off-peak.
| Same single cron entry drives these. Retention + integrity run after.
|--------------------------------------------------------------------------
*/
Schedule::command('backup:run --type=daily')
    ->dailyAt('02:00')->withoutOverlapping()->runInBackground();

Schedule::command('backup:run --type=weekly')
    ->weeklyOn(0, '03:00')->withoutOverlapping()->runInBackground(); // Sunday

Schedule::command('backup:run --type=monthly')
    ->monthlyOn(1, '04:00')->withoutOverlapping()->runInBackground();

Schedule::command('backup:clean')->dailyAt('05:00');

// Surfaces a stale/corrupt backup via a non-zero exit (cron emails the failure).
Schedule::command('backup:verify')->dailyAt('05:30');
