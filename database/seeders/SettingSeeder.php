<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\SocialLink;
use Database\Seeders\Concerns\LoadsSeedData;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    use LoadsSeedData;

    public function run(): void
    {
        $web = $this->loadData('web_settings')[0] ?? [];
        $emails = $this->loadData('web_setting_emails');
        $phones = $this->loadData('web_setting_phones');
        $addresses = $this->loadData('web_setting_addresses');
        $maps = $this->loadData('web_setting_maps');

        // Combine office addresses into a single text block.
        $addressText = collect($addresses)
            ->map(fn ($a) => trim(($a['label'] ?? '').': '.($a['address'] ?? '')))
            ->implode("\n");

        $settings = Setting::current();

        // Only update company-profile fields — never touch logo/favicon or the
        // SMTP (mail_*) settings configured from the CMS.
        $settings->update([
            'company_name' => $web['company_name'] ?? 'Equator Group',
            'email' => $emails[0]['email'] ?? null,
            'phone' => $phones[0]['phone'] ?? null,
            'address' => $this->nullable($addressText),
            'google_maps_embed' => $maps[0]['iframe_map'] ?? null,
        ]);

        // SEO/branding defaults — seed only when empty so we never clobber the
        // copy an admin has curated in the CMS (these fields are CMS-editable).
        $settings->update([
            'tagline' => $settings->tagline ?: ($web['tagline'] ?? null),
            'meta_title' => $settings->meta_title ?: ($web['meta_title'] ?? null),
            'meta_description' => $settings->meta_description ?: ($web['meta_description'] ?? null),
            'meta_keywords' => $settings->meta_keywords ?: ($web['meta_keywords'] ?? null),
        ]);

        // Social links from the legacy web_settings row.
        $social = [
            ['platform' => 'Instagram', 'url' => $web['instagram'] ?? null, 'icon_class' => 'bi bi-instagram'],
            ['platform' => 'LinkedIn', 'url' => $web['linkedin'] ?? null, 'icon_class' => 'bi bi-linkedin'],
            ['platform' => 'YouTube', 'url' => $web['youtube'] ?? null, 'icon_class' => 'bi bi-youtube'],
            ['platform' => 'Facebook', 'url' => $web['facebook'] ?? null, 'icon_class' => 'bi bi-facebook'],
            ['platform' => 'Twitter', 'url' => $web['twitter'] ?? null, 'icon_class' => 'bi bi-twitter-x'],
        ];

        $order = 1;
        foreach ($social as $item) {
            if (blank($item['url'])) {
                continue;
            }

            SocialLink::updateOrCreate(
                ['platform' => $item['platform']],
                [
                    'url' => $item['url'],
                    'icon_class' => $item['icon_class'],
                    'display_order' => $order++,
                    'status' => 'active',
                ]
            );
        }
    }
}
