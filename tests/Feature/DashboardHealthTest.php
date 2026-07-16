<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The dashboard surfaces an in-CMS "System Health" alert when background jobs
 * (transactional emails / notifications) have failed — so a silent send failure
 * is visible without leaving the admin panel.
 */
class DashboardHealthTest extends TestCase
{
    use RefreshDatabase;

    private function seedFailedJob(): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'Test failure',
            'failed_at' => now(),
        ]);
    }

    public function test_alert_shows_to_super_admin_when_a_job_failed(): void
    {
        $this->seedFailedJob();

        $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('background job(s) failed');
    }

    public function test_no_alert_when_there_are_no_failures(): void
    {
        $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('background job(s) failed');
    }

    public function test_alert_is_not_computed_for_non_super_admin(): void
    {
        $this->seedFailedJob();

        // A regular admin never sees the health alert (super-admin-only signal).
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('background job(s) failed');
    }
}
