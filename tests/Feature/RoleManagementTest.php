<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The role-management UI: only super admins reach it, roles carry a validated
 * permission set, the two system roles are protected, and a role created here
 * actually constrains an admin assigned to it.
 */
class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    private function super(): Admin
    {
        return Admin::factory()->superAdmin()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Access
    |--------------------------------------------------------------------------
    */

    public function test_super_admin_can_open_role_management(): void
    {
        $this->actingAs($this->super(), 'admin')
            ->get(route('admin.roles.index'))->assertOk();
    }

    public function test_regular_admin_cannot_access_role_management(): void
    {
        // The seeded `admin` role has no role.* permissions.
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.roles.index'))->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | CRUD
    |--------------------------------------------------------------------------
    */

    public function test_super_admin_creates_a_role_with_permissions(): void
    {
        $this->actingAs($this->super(), 'admin')
            ->post(route('admin.roles.store'), [
                'name' => 'content_editor',
                'permissions' => ['news.view', 'news.create', 'faq.view'],
            ])->assertRedirect(route('admin.roles.index'));

        $role = Role::where('name', 'content_editor')->where('guard_name', 'admin')->first();
        $this->assertNotNull($role);
        $this->assertEqualsCanonicalizing(['news.view', 'news.create', 'faq.view'],
            $role->permissions->pluck('name')->all());
    }

    public function test_role_name_must_be_slug_like_and_unique(): void
    {
        $super = $this->super();

        $this->actingAs($super, 'admin')
            ->post(route('admin.roles.store'), ['name' => 'Bad Name!'])
            ->assertSessionHasErrors('name');

        $this->actingAs($super, 'admin')
            ->post(route('admin.roles.store'), ['name' => 'admin'])
            ->assertSessionHasErrors('name');
    }

    public function test_unknown_permission_names_are_discarded(): void
    {
        $this->actingAs($this->super(), 'admin')
            ->post(route('admin.roles.store'), [
                'name' => 'weird',
                'permissions' => ['news.view', 'news.explode', 'not.real'],
            ])->assertSessionHasErrors('permissions.1'); // Rule::in rejects the bad ones
    }

    public function test_protected_roles_cannot_be_deleted(): void
    {
        $super = $this->super();
        $admin = Role::where('name', 'admin')->where('guard_name', 'admin')->first();

        $this->actingAs($super, 'admin')
            ->delete(route('admin.roles.destroy', $admin))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['name' => 'admin']);
    }

    public function test_a_role_in_use_cannot_be_deleted(): void
    {
        $super = $this->super();

        Role::findOrCreate('editor', 'admin');
        Admin::factory()->create(['role' => 'editor']); // model event assigns it

        $editor = Role::where('name', 'editor')->first();

        $this->actingAs($super, 'admin')
            ->delete(route('admin.roles.destroy', $editor))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['name' => 'editor']);
    }

    public function test_super_admin_role_keeps_all_permissions_even_if_form_trims_them(): void
    {
        $super = $this->super();
        $role = Role::where('name', 'super_admin')->where('guard_name', 'admin')->first();

        $this->actingAs($super, 'admin')
            ->put(route('admin.roles.update', $role), ['name' => 'super_admin', 'permissions' => ['news.view']])
            ->assertRedirect();

        $this->assertGreaterThan(1, $role->fresh()->permissions()->count());
    }

    /*
    |--------------------------------------------------------------------------
    | Integration: a custom role actually constrains its admins
    |--------------------------------------------------------------------------
    */

    public function test_admin_assigned_a_custom_role_is_restricted_to_it(): void
    {
        Role::findOrCreate('editor', 'admin')->syncPermissions(['news.view']);

        $editor = Admin::factory()->create(['role' => 'editor']);

        $this->actingAs($editor, 'admin')->get(route('admin.news.index'))->assertOk();
        $this->actingAs($editor, 'admin')->get(route('admin.services.index'))->assertForbidden();
    }

    public function test_admin_can_be_created_with_a_custom_role_through_the_form(): void
    {
        Role::findOrCreate('editor', 'admin')->syncPermissions(['news.view']);

        $this->actingAs($this->super(), 'admin')
            ->post(route('admin.admins.store'), [
                'name' => 'Edith',
                'email' => 'edith@example.test',
                'password' => 'secret1234',
                'password_confirmation' => 'secret1234',
                'role' => 'editor',
                'status' => 'active',
            ])->assertRedirect();

        $created = Admin::where('email', 'edith@example.test')->firstOrFail();
        $this->assertTrue($created->hasRole('editor'));
    }

    public function test_admin_cannot_be_created_with_a_nonexistent_role(): void
    {
        $this->actingAs($this->super(), 'admin')
            ->post(route('admin.admins.store'), [
                'name' => 'Ghost',
                'email' => 'ghost@example.test',
                'password' => 'secret1234',
                'password_confirmation' => 'secret1234',
                'role' => 'does_not_exist',
                'status' => 'active',
            ])->assertSessionHasErrors('role');
    }
}
