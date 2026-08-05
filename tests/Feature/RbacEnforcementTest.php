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
}
