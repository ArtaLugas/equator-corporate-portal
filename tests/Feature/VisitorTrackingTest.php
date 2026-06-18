<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_view_is_tracked(): void
    {
        $this->get('/')->assertOk();

        $this->assertDatabaseCount('visitors', 1);
    }

    public function test_admin_pages_are_not_tracked(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->assertDatabaseCount('visitors', 0);
    }

    public function test_dashboard_loads_with_visitor_chart(): void
    {
        Visitor::create([
            'ip_address' => '1.2.3.4',
            'url' => 'http://localhost',
            'visited_at' => now(),
        ]);

        $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Visitor Analytics');
    }
}
