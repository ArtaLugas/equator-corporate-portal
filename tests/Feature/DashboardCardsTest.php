<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The dashboard KPI row is permission-aware: it only ever links to modules the
 * admin can open, and fills spare slots with quick links to other permitted
 * modules — so a restricted role never meets a card that 403s.
 */
class DashboardCardsTest extends TestCase
{
    use RefreshDatabase;

    private function editorWith(array $permissions): Admin
    {
        $admin = Admin::factory()->create();
        $admin->syncRoles([]);
        Role::findOrCreate('editor', 'admin')->syncPermissions($permissions);
        $admin->assignRole('editor');

        return $admin;
    }

    public function test_super_admin_sees_the_users_stat_card(): void
    {
        $html = $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
            ->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString(route('admin.admins.index'), $html);
        $this->assertStringContainsString(route('admin.services.index'), $html);
    }

    public function test_regular_admin_sees_news_instead_of_users(): void
    {
        $html = $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.dashboard'))->assertOk()->getContent();

        // admin role lacks administrator.view, so the Users card is not shown...
        $this->assertStringNotContainsString(route('admin.admins.index'), $html);
        // ...and the content modules are.
        $this->assertStringContainsString(route('admin.news.index'), $html);
        $this->assertStringContainsString(route('admin.projects.index'), $html);
    }

    public function test_restricted_role_only_links_to_permitted_modules(): void
    {
        $editor = $this->editorWith(['news.view', 'faq.view', 'team.view']);

        $html = $this->actingAs($editor, 'admin')
            ->get(route('admin.dashboard'))->assertOk()->getContent();

        // Permitted modules appear (as a stat card or a quick link).
        $this->assertStringContainsString(route('admin.news.index'), $html);
        $this->assertStringContainsString(route('admin.faqs.index'), $html);
        $this->assertStringContainsString(route('admin.teams.index'), $html);

        // Modules the role cannot open must not be linked anywhere on the page.
        $this->assertStringNotContainsString(route('admin.services.index'), $html);
        $this->assertStringNotContainsString(route('admin.projects.index'), $html);
        $this->assertStringNotContainsString(route('admin.admins.index'), $html);
    }

    public function test_single_module_role_still_loads_without_dead_links(): void
    {
        $editor = $this->editorWith(['news.view']);

        $html = $this->actingAs($editor, 'admin')
            ->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString(route('admin.news.index'), $html);
        $this->assertStringNotContainsString(route('admin.services.index'), $html);
    }
}
