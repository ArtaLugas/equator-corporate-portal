<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_own_profile(): void
    {
        $admin = Admin::factory()->create(['name' => 'Old Name']);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.account.profile.update'), [
                'name' => 'New Name',
                'email' => $admin->email,
            ])
            ->assertRedirect();

        $this->assertSame('New Name', $admin->fresh()->name);
    }

    public function test_admin_can_change_password_with_correct_current_password(): void
    {
        $admin = Admin::factory()->create(['password' => Hash::make('oldpass123')]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.account.password.update'), [
                'current_password' => 'oldpass123',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('newpass123', $admin->fresh()->password));
    }

    public function test_password_change_fails_with_wrong_current_password(): void
    {
        $admin = Admin::factory()->create(['password' => Hash::make('oldpass123')]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.account.password.update'), [
                'current_password' => 'wrongpass',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('oldpass123', $admin->fresh()->password));
    }
}
