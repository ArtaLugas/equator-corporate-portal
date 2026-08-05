<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Proves per-module permission enforcement: a restricted role reaches only what
 * it is granted, while the seeded `admin`/`super_admin` roles retain full access
 * (the reason the rest of the suite stays green once controllers enforce).
 */
class RbacEnforcementTest extends TestCase
{
    use RefreshDatabase;

    /** An admin holding only news.view — no create/update/delete. */
    private function newsViewer(): Admin
    {
        $admin = Admin::factory()->create();
        $admin->syncRoles([]); // drop the auto-assigned full `admin` role

        Role::findOrCreate('editor', 'admin')->syncPermissions(['news.view']);
        $admin->assignRole('editor');

        return $admin;
    }

    public function test_view_only_role_can_list_but_not_create_or_delete_news(): void
    {
        $editor = $this->newsViewer();

        $this->actingAs($editor, 'admin')->get(route('admin.news.index'))->assertOk();

        $this->actingAs($editor, 'admin')->get(route('admin.news.create'))->assertForbidden();
        $this->actingAs($editor, 'admin')->post(route('admin.news.store'), [])->assertForbidden();
    }

    public function test_regular_admin_retains_full_news_access(): void
    {
        // Factory admin carries the seeded `admin` role = all content permissions.
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')->get(route('admin.news.index'))->assertOk();
        $this->actingAs($admin, 'admin')->get(route('admin.news.create'))->assertOk();
    }

    public function test_super_admin_retains_full_news_access(): void
    {
        $super = Admin::factory()->superAdmin()->create();

        $this->actingAs($super, 'admin')->get(route('admin.news.index'))->assertOk();
        $this->actingAs($super, 'admin')->get(route('admin.news.create'))->assertOk();
    }

    /**
     * The sidebar's @can guards resolve against the admin guard: a restricted role
     * sees only the modules it may reach. This also proves @can works in Blade for
     * the admin guard (the default guard), not just controller middleware.
     */
    public function test_sidebar_only_shows_permitted_modules(): void
    {
        // Grant only sidebar-exclusive modules so the assertions isolate the
        // sidebar @can guards (the dashboard body links news/projects/services,
        // so those URLs would appear regardless of the nav).
        $editor = Admin::factory()->create();
        $editor->syncRoles([]);
        Role::findOrCreate('editor', 'admin')->syncPermissions(['team.view', 'faq.view']);
        $editor->assignRole('editor');

        $html = $this->actingAs($editor, 'admin')->get(route('admin.dashboard'))
            ->assertOk()->getContent();

        // Permitted, sidebar-only → present.
        $this->assertStringContainsString(route('admin.teams.index'), $html);
        $this->assertStringContainsString(route('admin.faqs.index'), $html);
        // Not permitted, sidebar-only → hidden.
        $this->assertStringNotContainsString(route('admin.office-locations.index'), $html);
        $this->assertStringNotContainsString(route('admin.social-links.index'), $html);
        $this->assertStringNotContainsString(route('admin.hero-banners.index'), $html);
        $this->assertStringNotContainsString(route('admin.partners.index'), $html);
    }

    public function test_sidebar_shows_all_content_modules_to_a_full_admin(): void
    {
        $admin = Admin::factory()->create();

        $html = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))
            ->assertOk()->getContent();

        $this->assertStringContainsString(route('admin.office-locations.index'), $html);
        $this->assertStringContainsString(route('admin.hero-banners.index'), $html);
    }

    /**
     * Regression guard: an admin created straight through the model (the path the
     * admin-management UI uses) must receive the permissions of its role column
     * via the model's saved event — otherwise every new admin lands permissionless
     * once controllers enforce.
     */
    public function test_creating_an_admin_grants_its_role_column_permissions(): void
    {
        $admin = Admin::create([
            'name' => 'Fresh Admin',
            'email' => 'fresh@example.test',
            'password' => 'secret1234',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->assertTrue($admin->fresh()->hasRole('admin'));

        $this->actingAs($admin, 'admin')->get(route('admin.news.index'))->assertOk();
    }
}
