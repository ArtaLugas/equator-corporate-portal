<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Opt-in two-factor enrollment from the Account page. Flow:
 *   enable  -> generate secret + recovery codes (unconfirmed, does not gate login)
 *   confirm -> verify a code from the authenticator app -> two_factor_confirmed_at
 *   disable -> clear everything (requires the current password)
 */
class TwoFactorController extends Controller
{
    public function enable(Request $request)
    {
        $admin = auth('admin')->user();

        if ($admin->hasTwoFactorEnabled()) {
            return back();
        }

        $admin->two_factor_secret = (new TwoFactorAuthenticator)->generateSecretKey();
        $admin->two_factor_recovery_codes = $this->newRecoveryCodes();
        $admin->two_factor_confirmed_at = null;
        $admin->save();

        activity_log('Account', 'Started two-factor setup');

        return back()->with('twofa_setup', true);
    }

    public function confirm(Request $request)
    {
        $admin = auth('admin')->user();

        $request->validate(['code' => ['required', 'string']]);

        if (blank($admin->two_factor_secret)) {
            return back()->withErrors(['code' => 'Start two-factor setup first.']);
        }

        if (! (new TwoFactorAuthenticator)->verify($admin->two_factor_secret, $request->code)) {
            return back()
                ->with('twofa_setup', true)
                ->withErrors(['code' => 'That code is invalid or expired. Try again.']);
        }

        $admin->two_factor_confirmed_at = now();
        $admin->save();

        activity_log('Account', 'Enabled two-factor authentication');

        return back()->with('success', 'Two-factor authentication is now enabled.');
    }

    public function disable(Request $request)
    {
        $request->validate(
            ['current_password' => ['required', 'current_password:admin']],
            ['current_password.current_password' => 'The current password is incorrect.']
        );

        $admin = auth('admin')->user();

        $admin->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        activity_log('Account', 'Disabled two-factor authentication');

        return back()->with('success', 'Two-factor authentication has been disabled.');
    }

    public function recoveryCodes(Request $request)
    {
        $admin = auth('admin')->user();

        if (! $admin->hasTwoFactorEnabled()) {
            return back();
        }

        $admin->two_factor_recovery_codes = $this->newRecoveryCodes();
        $admin->save();

        activity_log('Account', 'Regenerated two-factor recovery codes');

        return back()->with('success', 'New recovery codes generated.')->with('twofa_show_codes', true);
    }

    /** Eight single-use recovery codes in XXXX-XXXX format. */
    private function newRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(4).'-'.Str::random(4)))
            ->all();
    }
}
