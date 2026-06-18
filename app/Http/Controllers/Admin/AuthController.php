<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Rules\Turnstile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LOGIN PAGE
    |--------------------------------------------------------------------------
    */

    public function login()
    {
        // Prevent logged-in admin accessing login page
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATE
    |--------------------------------------------------------------------------
    */

    public function authenticate(Request $request)
    {
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];

        // CAPTCHA login hanya diwajibkan bila Turnstile dikonfigurasi.
        // (Sengaja TIDAK "selalu wajib" agar admin tidak terkunci bila kunci belum diisi.)
        $captchaEnabled = filled(config('services.turnstile.secret_key'));

        if ($captchaEnabled) {
            $rules['cf-turnstile-response'] = ['required', new Turnstile($request->ip())];
        }

        $validated = $request->validate($rules, [
            'cf-turnstile-response.required' => 'Please complete the security verification first.',
        ]);

        // Rate limiting: cegah brute-force (5 percobaan / menit per email + IP).
        $throttleKey = Str::lower($validated['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
            'status' => 'active',
        ];

        $remember = $request->boolean('remember');

        if (Auth::guard('admin')->attempt($credentials, $remember)) {

            RateLimiter::clear($throttleKey);

            $request->session()->regenerate();

            /** @var Admin|null $admin */
            $admin = Auth::guard('admin')->user();

            if ($admin) {
                $admin->update([
                    'last_login_at' => now(),
                ]);
            }

            return redirect()
                ->intended(route('admin.dashboard'));
        }

        // Gagal: catat percobaan (lockout selama 60 detik setelah batas).
        RateLimiter::hit($throttleKey, 60);

        return back()
            ->withErrors([
                'email' => 'The email address or password is incorrect.',
            ])
            ->onlyInput('email');
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()
            ->route('admin.login');
    }
}
