<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TwoFactorEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::factory()->create([
            'status' => 'active',
            'password' => Hash::make('secret-123'),
        ]);
    }

    public function test_enable_creates_an_unconfirmed_secret_that_does_not_gate_login(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.account.2fa.enable'))
            ->assertRedirect();

        $admin->refresh();
        $this->assertNotNull($admin->two_factor_secret);
        $this->assertNotEmpty($admin->two_factor_recovery_codes);
        $this->assertNull($admin->two_factor_confirmed_at);
        $this->assertFalse($admin->hasTwoFactorEnabled());
    }

    public function test_confirm_with_a_valid_code_enables_two_factor(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->post(route('admin.account.2fa.enable'));
        $secret = $admin->fresh()->two_factor_secret;
        $code = (new TwoFactorAuthenticator)->currentCode($secret);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.account.2fa.confirm'), ['code' => $code])
            ->assertSessionHasNoErrors();

        $this->assertTrue($admin->fresh()->hasTwoFactorEnabled());
    }

    public function test_confirm_with_an_invalid_code_is_rejected(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'admin')->post(route('admin.account.2fa.enable'));

        $this->actingAs($admin, 'admin')
            ->post(route('admin.account.2fa.confirm'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertFalse($admin->fresh()->hasTwoFactorEnabled());
    }

    public function test_disable_requires_the_current_password(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'admin')->post(route('admin.account.2fa.enable'));
        $secret = $admin->fresh()->two_factor_secret;
        $this->actingAs($admin, 'admin')->post(route('admin.account.2fa.confirm'), [
            'code' => (new TwoFactorAuthenticator)->currentCode($secret),
        ]);

        // Wrong password → still enabled.
        $this->actingAs($admin, 'admin')
            ->delete(route('admin.account.2fa.disable'), ['current_password' => 'wrong'])
            ->assertSessionHasErrors('current_password');
        $this->assertTrue($admin->fresh()->hasTwoFactorEnabled());

        // Correct password → disabled.
        $this->actingAs($admin, 'admin')
            ->delete(route('admin.account.2fa.disable'), ['current_password' => 'secret-123'])
            ->assertSessionHasNoErrors();
        $this->assertFalse($admin->fresh()->hasTwoFactorEnabled());
    }
}
