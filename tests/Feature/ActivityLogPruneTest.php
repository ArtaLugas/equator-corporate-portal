<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Audit-log retention: the scheduled `model:prune` deletes activity_logs rows
 * older than cms.activity_log_retention_days (default 365). Retention is strictly
 * time-based — there is deliberately no manual "clear log" action.
 */
class ActivityLogPruneTest extends TestCase
{
    use RefreshDatabase;

    private function logAged(int $daysAgo, string $description): ActivityLog
    {
        $log = ActivityLog::create([
            'module' => 'Test',
            'description' => $description,
            'ip_address' => '127.0.0.1',
        ]);

        // created_at is not fillable and is stamped on insert — override it quietly.
        $log->forceFill(['created_at' => now()->subDays($daysAgo)])->saveQuietly();

        return $log;
    }

    public function test_prune_deletes_rows_older_than_retention_and_keeps_recent(): void
    {
        config(['cms.activity_log_retention_days' => 365]);

        $this->logAged(400, 'expired');
        $this->logAged(10, 'recent');

        $this->artisan('model:prune', ['--model' => [ActivityLog::class]])
            ->assertSuccessful();

        $this->assertDatabaseMissing('activity_logs', ['description' => 'expired']);
        $this->assertDatabaseHas('activity_logs', ['description' => 'recent']);
    }

    public function test_retention_window_is_configurable(): void
    {
        config(['cms.activity_log_retention_days' => 30]);

        $this->logAged(45, 'gone');   // older than 30 days → pruned
        $this->logAged(20, 'stays');  // within 30 days → kept

        $this->artisan('model:prune', ['--model' => [ActivityLog::class]])
            ->assertSuccessful();

        $this->assertDatabaseMissing('activity_logs', ['description' => 'gone']);
        $this->assertDatabaseHas('activity_logs', ['description' => 'stays']);
    }

    public function test_zero_retention_keeps_logs_indefinitely(): void
    {
        config(['cms.activity_log_retention_days' => 0]);

        $this->logAged(1000, 'ancient');

        $this->artisan('model:prune', ['--model' => [ActivityLog::class]])
            ->assertSuccessful();

        $this->assertDatabaseHas('activity_logs', ['description' => 'ancient']);
    }
}
