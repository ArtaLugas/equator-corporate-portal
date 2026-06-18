<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@equator.test',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->post(route('admin.authenticate'), [
            'email' => 'admin@equator.test',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        Admin::factory()->create([
            'email' => 'admin@equator.test',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->post(route('admin.authenticate'), [
            'email' => 'admin@equator.test',
            'password' => 'wrong',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_inactive_admin_cannot_login(): void
    {
        Admin::factory()->inactive()->create([
            'email' => 'inactive@equator.test',
            'password' => Hash::make('secret123'),
        ]);

        $this->post(route('admin.authenticate'), [
            'email' => 'inactive@equator.test',
            'password' => 'secret123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }
}
