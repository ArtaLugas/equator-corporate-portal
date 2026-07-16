<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Rules\Turnstile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Self-service admin password reset ("Forgot password"). Uses the "admins"
 * password broker (config/auth.php); the reset link email is delivered through
 * the CMS Brevo mailer via Admin::sendPasswordResetNotification().
 */
class PasswordResetController extends Controller
{
    public function showForgotForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $rules = ['email' => ['required', 'email']];

        // Same CAPTCHA policy as login: required only when Turnstile is configured.
        if (filled(config('services.turnstile.secret_key'))) {
            $rules['cf-turnstile-response'] = ['required', new Turnstile($request->ip())];
        }

        $validated = $request->validate($rules, [
            'cf-turnstile-response.required' => 'Please complete the security verification first.',
        ]);

        // Only active admins receive a link; the broker calls the model's
        // sendPasswordResetNotification() (queued Brevo email).
        Password::broker('admins')->sendResetLink([
            'email' => $validated['email'],
            'status' => 'active',
        ]);

        // Always report the same result to avoid revealing which emails exist.
        return back()->with('status', 'If that email is registered, a password reset link has been sent.');
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('admin.auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($admin, $password) {
                // The Admin model's 'hashed' cast hashes the plain value on save.
                $admin->forceFill(['password' => $password])
                    ->setRememberToken(Str::random(60));
                $admin->save();

                activity_log('Admin', 'Password reset via email: '.$admin->email);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('admin.login')
                ->with('status', 'Your password has been reset. Please sign in.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
