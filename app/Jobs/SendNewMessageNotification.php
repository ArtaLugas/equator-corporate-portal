<?php

namespace App\Jobs;

use App\Mail\NewContactMessageMail;
use App\Models\Message;
use App\Services\BrevoMailConfigurator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendNewMessageNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public Message $message) {}

    public function handle(): void
    {
        $mailer = BrevoMailConfigurator::resolveMailer();
        $office = BrevoMailConfigurator::officeEmail();

        Mail::mailer($mailer)
            ->to($office)
            ->send(new NewContactMessageMail($this->message));
    }

    /**
     * Surface a final send failure so a missed office notification is not silent
     * (see SendContactReply::failed()).
     */
    public function failed(Throwable $e): void
    {
        report($e);

        Log::error('New-message office notification failed to send', [
            'message_id' => $this->message->id ?? null,
            'error' => $e->getMessage(),
        ]);
    }
}
