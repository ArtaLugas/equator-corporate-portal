<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_report_page_loads(): void
    {
        $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
            ->get(route('admin.dashboard.report'))
            ->assertOk()
            ->assertSee('Dashboard Report');
    }

    public function test_excel_export_downloads_xlsx(): void
    {
        $response = $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.dashboard.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Force the streamed content to render without error.
        $this->assertNotEmpty($response->streamedContent());
    }

    public function test_dashboard_shows_recent_activity_only_to_super_admin(): void
    {
        // Regular admin: restricted message.
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('restricted to super admins');

        // Super admin: sees the View all link to activity logs.
        $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('View all');
    }
}
