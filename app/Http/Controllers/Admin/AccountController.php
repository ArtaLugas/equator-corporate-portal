<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | EDIT (Account Settings page)
    |--------------------------------------------------------------------------
    */

    public function edit()
    {
        $admin = auth('admin')->user();

        // When a secret exists but is not yet confirmed, the account page shows the
        // enrollment panel (QR + manual key + recovery codes).
        $twoFactor = null;

        if (filled($admin->two_factor_secret) && ! $admin->hasTwoFactorEnabled()) {
            $twoFactor = [
                'secret' => $admin->two_factor_secret,
                'uri' => (new TwoFactorAuthenticator)->otpauthUri(
                    app_setting('company_name', 'Equator Group'),
                    $admin->email,
                    $admin->two_factor_secret,
                ),
                'recovery' => $admin->two_factor_recovery_codes ?? [],
            ];
        }

        return view('admin.account.edit', compact('admin', 'twoFactor'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PROFILE (name, email, avatar)
    |--------------------------------------------------------------------------
    */

    public function updateProfile(Request $request)
    {
        $admin = auth('admin')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => [
                'required', 'email', 'max:191',
                Rule::unique('admins', 'email')->ignore($admin->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Avatar: replace (with cleanup) or remove.
        if ($request->hasFile('avatar')) {
            $newPath = $this->uploadImage($request->file('avatar'), 'avatars');

            if ($admin->avatar) {
                Storage::disk('public')->delete($admin->avatar);
            }

            $validated['avatar'] = $newPath;

        } elseif ($request->boolean('remove_image')) {
            if ($admin->avatar) {
                Storage::disk('public')->delete($admin->avatar);
            }

            $validated['avatar'] = null;

        } else {
            unset($validated['avatar']);
        }

        $admin->update($validated);

        activity_log('Account', 'Updated own profile');

        return back()->with('success', 'Profile updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PASSWORD
    |--------------------------------------------------------------------------
    */

    public function updatePassword(Request $request)
    {
        $admin = auth('admin')->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', 'confirmed', 'min:8', 'different:current_password'],
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
        ]);

        // Password is auto-hashed via the model cast.
        $admin->update(['password' => $validated['password']]);

        activity_log('Account', 'Changed own password');

        return back()->with('success', 'Password changed successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Upload Image
    |--------------------------------------------------------------------------
    */

    private function uploadImage(UploadedFile $image, string $folder): string
    {
        $filename = time().'-'.Str::random(6).'.'.$image->getClientOriginalExtension();

        return $image->storeAs($folder, $filename, 'public');
    }
}
