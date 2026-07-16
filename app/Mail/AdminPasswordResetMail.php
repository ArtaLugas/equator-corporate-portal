<?php

namespace App\Mail;

use App\Models\Admin;
use App\Services\BrevoMailConfigurator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Password-reset link sent to an admin who used "Forgot password". Sent through
 * the CMS-configured Brevo mailer (see SendPasswordResetLink).
 */
class AdminPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Admin $admin,
        public string $resetUrl,
        public int $expiresMinutes,
    ) {}

    public function envelope(): Envelope
    {
        $from = BrevoMailConfigurator::fromAddress();

        return new Envelope(
            from: new Address($from['address'], $from['name']),
            subject: 'Reset your '.app_setting('company_name', 'Equator Group').' password',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.password-reset',
            with: [
                'admin' => $this->admin,
                'resetUrl' => $this->resetUrl,
                'expiresMinutes' => $this->expiresMinutes,
            ],
        );
    }
}
