<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Applies the Brevo SMTP configuration stored in the CMS Settings to the
 * runtime mail config, so outgoing mail is never hardcoded and can be changed
 * from the admin panel without a redeploy.
 */
class BrevoMailConfigurator
{
    /**
     * Configure the "brevo" mailer from settings and return the mailer name
     * that should be used to send mail. Falls back to the default mailer when
     * SMTP settings are incomplete.
     */
    public static function resolveMailer(): string
    {
        $settings = Setting::current();

        // Without host + username we cannot use Brevo; fall back to default.
        if (blank($settings->mail_host) || blank($settings->mail_username)) {
            return config('mail.default');
        }

        config([
            'mail.mailers.brevo.host' => $settings->mail_host,
            'mail.mailers.brevo.port' => (int) ($settings->mail_port ?: 587),
            'mail.mailers.brevo.username' => $settings->mail_username,
            'mail.mailers.brevo.password' => $settings->mail_password,
            'mail.mailers.brevo.encryption' => $settings->mail_encryption ?: 'tls',
        ]);

        return 'brevo';
    }

    /**
     * The configured "from" address/name (settings → env fallback).
     *
     * @return array{address: string, name: string}
     */
    public static function fromAddress(): array
    {
        $settings = Setting::current();

        return [
            'address' => $settings->mail_from_address
                ?: config('mail.from.address', 'no-reply@equatorgroup.id'),
            'name' => $settings->mail_from_name
                ?: config('mail.from.name', config('app.name')),
        ];
    }

    /**
     * The internal office inbox that receives new-message notifications.
     */
    public static function officeEmail(): string
    {
        return Setting::current()->office_email ?: 'office@equatorgroup.id';
    }
}
