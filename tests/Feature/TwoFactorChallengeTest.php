<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TwoFactorChallengeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Turnstile is unrelated to the 2FA flow; skip the CAPTCHA branch.
        config(['services.turnstile.secret_key' => null]);
    }

    private function twoFactorAdmin(string $secret, array $recovery = []): Admin
    {
        $admin = Admin::factory()->create([
            'status' => 'active',
            'password' => Hash::make('secret-123'),
        ]);

        $admin->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recovery ?: ['ABCD-1234', 'WXYZ-5678'],
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $admin;
    }

    public function test_login_with_2fa_redirects_to_challenge_not_dashboard(): void
    {
        $secret = (new TwoFactorAuthenticator)->generateSecretKey();
        $this->twoFactorAdmin($secret);

        $this->post(route('admin.authenticate'), [
            'email' => Admin::first()->email,
            'password' => 'secret-123',
        ])->assertRedirect(route('admin.two-factor.login'));

        $this->assertGuest('admin');
    }

    public function test_valid_totp_code_completes_login(): void
    {
        $svc = new TwoFactorAuthenticator;
        $secret = $svc->generateSecretKey();
        $admin = $this->twoFactorAdmin($secret);

        $this->post(route('admin.authenticate'), [
            'email' => $admin->email,
            'password' => 'secret-123',
        ]);

        $this->post(route('admin.two-factor.login.store'), ['code' => $svc->currentCode($secret)])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_invalid_code_is_rejected_and_stays_guest(): void
    {
        $secret = (new TwoFactorAuthenticator)->generateSecretKey();
        $admin = $this->twoFactorAdmin($secret);

        $this->post(route('admin.authenticate'), ['email' => $admin->email, 'password' => 'secret-123']);

        $this->post(route('admin.two-factor.login.store'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertGuest('admin');
    }

    public function test_recovery_code_completes_login_and_is_consumed(): void
    {
        $secret = (new TwoFactorAuthenticator)->generateSecretKey();
        $admin = $this->twoFactorAdmin($secret, ['ABCD-1234', 'WXYZ-5678']);

        $this->post(route('admin.authenticate'), ['email' => $admin->email, 'password' => 'secret-123']);

        $this->post(route('admin.two-factor.login.store'), ['code' => 'ABCD-1234'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');

        $remaining = $admin->fresh()->two_factor_recovery_codes;
        $this->assertNotContains('ABCD-1234', $remaining);
        $this->assertContains('WXYZ-5678', $remaining);
    }

    public function test_challenge_page_not_accessible_without_pending_login(): void
    {
        $this->get(route('admin.two-factor.login'))->assertRedirect(route('admin.login'));
    }
}
