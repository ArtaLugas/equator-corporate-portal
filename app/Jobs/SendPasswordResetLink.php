<?php

namespace App\Jobs;

use App\Mail\AdminPasswordResetMail;
use App\Models\Admin;
use App\Services\BrevoMailConfigurator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Delivers an admin password-reset link through the CMS-configured Brevo mailer
 * (the same transport as the other transactional emails), queued so the request
 * returns instantly.
 */
class SendPasswordResetLink implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public Admin $admin,
        public string $url,
        public int $expiresMinutes,
    ) {}

    public function handle(): void
    {
        $mailer = BrevoMailConfigurator::resolveMailer();

        // Passing the model lets the mailer read its email + name properties
        // (single-argument call, matching the Mailer contract).
        Mail::mailer($mailer)
            ->to($this->admin)
            ->send(new AdminPasswordResetMail($this->admin, $this->url, $this->expiresMinutes));
    }

    public function failed(Throwable $e): void
    {
        report($e);

        Log::error('Password reset email failed to send', [
            'admin_id' => $this->admin->id ?? null,
            'recipient' => $this->admin->email ?? null,
            'error' => $e->getMessage(),
        ]);
    }
}
