<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): Admin
    {
        return Admin::factory()->superAdmin()->create();
    }

    public function test_super_admin_can_view_admin_list(): void
    {
        $this->actingAs($this->superAdmin(), 'admin')
            ->get(route('admin.admins.index'))
            ->assertOk();
    }

    public function test_regular_admin_cannot_access_admin_management(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.admins.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_admin(): void
    {
        $this->actingAs($this->superAdmin(), 'admin')
            ->post(route('admin.admins.store'), [
                'name' => 'New Admin',
                'email' => 'new@equator.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'admin',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.admins.index'));

        $this->assertDatabaseHas('admins', [
            'email' => 'new@equator.test',
            'role' => 'admin',
        ]);
    }

    public function test_cannot_create_admin_with_duplicate_email(): void
    {
        $existing = Admin::factory()->create(['email' => 'dupe@equator.test']);

        $this->actingAs($this->superAdmin(), 'admin')
            ->post(route('admin.admins.store'), [
                'name' => 'Dupe',
                'email' => 'dupe@equator.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'admin',
                'status' => 'active',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('admins', 2); // super admin + existing
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $super = $this->superAdmin();
        // a second super admin so the "last super admin" guard isn't the blocker
        Admin::factory()->superAdmin()->create();

        $this->actingAs($super, 'admin')
            ->delete(route('admin.admins.destroy', $super))
            ->assertForbidden();

        $this->assertDatabaseHas('admins', ['id' => $super->id]);
    }

    public function test_cannot_delete_last_active_super_admin(): void
    {
        // Only one ACTIVE super admin (the target).
        $lastSuper = Admin::factory()->superAdmin()->create(['status' => 'active']);

        // An inactive super admin acts (actingAs bypasses login; this isolates
        // the "last active super admin" guard from the self-delete guard).
        $actor = Admin::factory()->superAdmin()->inactive()->create();

        $this->actingAs($actor, 'admin')
            ->delete(route('admin.admins.destroy', $lastSuper))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('admins', ['id' => $lastSuper->id]);
    }

    public function test_super_admin_can_delete_another_admin(): void
    {
        $target = Admin::factory()->create();

        $this->actingAs($this->superAdmin(), 'admin')
            ->delete(route('admin.admins.destroy', $target))
            ->assertRedirect();

        $this->assertDatabaseMissing('admins', ['id' => $target->id]);
    }
}
