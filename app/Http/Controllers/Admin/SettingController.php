<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Email / SMTP settings are sensitive — restrict to super admins.
     */
    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth('admin')->user()?->isSuperAdmin(), 403);
    }

    /*
    |--------------------------------------------------------------------------
    | GENERAL (Company Profile) SETTINGS
    |--------------------------------------------------------------------------
    */

    public function general()
    {
        $settings = Setting::current();

        return view('admin.settings.general', compact('settings'));
    }

    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['nullable', 'string', 'max:191'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,ico,svg', 'max:1024'],
            // Contact (address/phone/email/map) kini dikelola di Office Locations — sumber tunggal.
            'meta_title' => ['nullable', 'string', 'max:191'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            // SEO & Analytics (IDs come from here — never hardcoded).
            'ga4_measurement_id' => ['nullable', 'string', 'max:32', 'regex:/^G-[A-Z0-9]+$/i'],
            'gsc_verification' => ['nullable', 'string', 'max:191'],
        ]);

        $settings = Setting::current();

        // Handle logo / favicon uploads (replace + cleanup, or remove).
        foreach (['logo', 'favicon'] as $field) {

            if ($request->hasFile($field)) {
                $newPath = $this->uploadImage($request->file($field), 'settings');

                if ($settings->{$field}) {
                    Storage::disk('public')->delete($settings->{$field});
                }

                $validated[$field] = $newPath;

            } elseif ($request->boolean('remove_'.$field)) {

                if ($settings->{$field}) {
                    Storage::disk('public')->delete($settings->{$field});
                }

                $validated[$field] = null;

            } else {
                // Keep existing file when nothing changed.
                unset($validated[$field]);
            }
        }

        $settings->update($validated);

        activity_log('Settings', 'Updated general (company profile) settings');

        return back()->with('success', 'Settings saved successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | EMAIL (Brevo SMTP) SETTINGS
    |--------------------------------------------------------------------------
    */

    public function email()
    {
        $this->authorizeSuperAdmin();

        $settings = Setting::current();

        return view('admin.settings.email', compact('settings'));
    }

    public function updateEmail(Request $request)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'mail_host' => ['nullable', 'string', 'max:191'],
            'mail_port' => ['nullable', 'numeric'],
            'mail_username' => ['nullable', 'string', 'max:191'],
            'mail_password' => ['nullable', 'string', 'max:191'],
            'mail_encryption' => ['nullable', 'in:tls,ssl'],
            'mail_from_address' => ['nullable', 'email', 'max:191'],
            'mail_from_name' => ['nullable', 'string', 'max:191'],
            'office_email' => ['nullable', 'email', 'max:191'],
        ]);

        $settings = Setting::current();

        // Keep the existing password when the field is left blank.
        if (blank($validated['mail_password'] ?? null)) {
            unset($validated['mail_password']);
        }

        $settings->update($validated);

        activity_log('Settings', 'Updated email (Brevo SMTP) settings');

        return back()->with('success', 'Email settings saved successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Upload Image
    |--------------------------------------------------------------------------
    */

    private function uploadImage(UploadedFile $image, string $folder): string
    {
        return app(ImageService::class)->store($image, $folder);
    }
}
