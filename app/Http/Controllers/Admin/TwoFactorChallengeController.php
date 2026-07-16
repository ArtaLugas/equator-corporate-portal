<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Second-step login challenge. Reachable only when AuthController has stashed a
 * "2fa.pending_id" in the session after a correct password — the admin is NOT
 * authenticated until a valid TOTP or recovery code is supplied here.
 */
class TwoFactorChallengeController extends Controller
{
    public function create(Request $request)
    {
        if (! $request->session()->has('2fa.pending_id')) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.two-factor-challenge');
    }

    public function store(Request $request)
    {
        $pendingId = $request->session()->get('2fa.pending_id');

        if (! $pendingId) {
            return redirect()->route('admin.login');
        }

        $request->validate(['code' => ['required', 'string']]);

        $admin = Admin::find($pendingId);

        if (! $admin || ! $admin->hasTwoFactorEnabled()) {
            $request->session()->forget(['2fa.pending_id', '2fa.remember']);

            return redirect()->route('admin.login');
        }

        // Throttle challenge attempts (per admin + IP).
        $key = '2fa|'.$admin->id.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'code' => "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        if (! $this->passes($admin, trim($request->code))) {
            RateLimiter::hit($key, 60);

            return back()->withErrors(['code' => 'That code is invalid. Try again.']);
        }

        RateLimiter::clear($key);

        $remember = (bool) $request->session()->pull('2fa.remember', false);
        $request->session()->forget('2fa.pending_id');

        Auth::guard('admin')->loginUsingId($admin->id, $remember);
        $request->session()->regenerate();

        $admin->update(['last_login_at' => now()]);
        activity_log('Admin', 'Two-factor challenge passed: '.$admin->email);

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Accept either a valid TOTP code or an unused recovery code (which is then
     * consumed so it cannot be reused).
     */
    private function passes(Admin $admin, string $input): bool
    {
        if ((new TwoFactorAuthenticator)->verify($admin->two_factor_secret, $input)) {
            return true;
        }

        $codes = $admin->two_factor_recovery_codes ?? [];
        $normalized = strtoupper($input);

        foreach ($codes as $code) {
            if (hash_equals(strtoupper($code), $normalized)) {
                $admin->two_factor_recovery_codes = array_values(array_filter(
                    $codes,
                    fn ($c) => strtoupper($c) !== $normalized,
                ));
                $admin->save();

                return true;
            }
        }

        return false;
    }
}
