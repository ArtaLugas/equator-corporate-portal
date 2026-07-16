<?php

namespace Tests\Feature;

use App\Jobs\SendPasswordResetLink;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Admin self-service password reset (Forgot password → Brevo email → reset form).
 * Uses the "admins" broker. Turnstile is unconfigured in the test env, so the
 * CAPTCHA rule is skipped (mirrors login test behaviour).
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Turnstile is unrelated to reset logic; disable it so the CAPTCHA rule is
        // skipped (the controller only enforces it when a secret key is present).
        config(['services.turnstile.secret_key' => null]);
    }

    private function admin(array $overrides = []): Admin
    {
        return Admin::factory()->create(array_merge([
            'email' => 'admin@example.com',
            'status' => 'active',
            'password' => Hash::make('old-password-123'),
        ], $overrides));
    }

    public function test_forgot_form_renders(): void
    {
        $this->get(route('admin.password.request'))
            ->assertOk()
            ->assertSee('Forgot Password');
    }

    public function test_reset_link_creates_token_and_queues_brevo_email(): void
    {
        Queue::fake();
        $this->admin();

        $this->post(route('admin.password.email'), ['email' => 'admin@example.com'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'admin@example.com']);
        Queue::assertPushed(SendPasswordResetLink::class);
    }

    public function test_unknown_email_reveals_nothing_and_sends_no_link(): void
    {
        Queue::fake();

        $this->post(route('admin.password.email'), ['email' => 'nobody@example.com'])
            ->assertSessionHas('status'); // same generic message → no enumeration

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'nobody@example.com']);
        Queue::assertNotPushed(SendPasswordResetLink::class);
    }

    public function test_inactive_admin_gets_no_reset_link(): void
    {
        Queue::fake();
        $this->admin(['email' => 'off@example.com', 'status' => 'inactive']);

        $this->post(route('admin.password.email'), ['email' => 'off@example.com'])
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'off@example.com']);
        Queue::assertNotPushed(SendPasswordResetLink::class);
    }

    public function test_valid_token_resets_the_password(): void
    {
        $admin = $this->admin();
        $token = Password::broker('admins')->createToken($admin);

        $this->post(route('admin.password.update'), [
            'token' => $token,
            'email' => $admin->email,
            'password' => 'brand-new-pass-9',
            'password_confirmation' => 'brand-new-pass-9',
        ])->assertRedirect(route('admin.login'));

        $this->assertTrue(Hash::check('brand-new-pass-9', $admin->fresh()->password));
    }

    public function test_invalid_token_is_rejected(): void
    {
        $admin = $this->admin();

        $this->post(route('admin.password.update'), [
            'token' => 'totally-invalid-token',
            'email' => $admin->email,
            'password' => 'brand-new-pass-9',
            'password_confirmation' => 'brand-new-pass-9',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('old-password-123', $admin->fresh()->password));
    }
}
