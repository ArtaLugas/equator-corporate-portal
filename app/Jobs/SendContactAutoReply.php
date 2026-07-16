<?php

namespace App\Jobs;

use App\Mail\ContactAutoReplyMail;
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

/**
 * Sends the visitor the auto-reply confirmation in the website's active locale
 * (captured at submit time, since the queue worker runs later in a neutral locale).
 */
class SendContactAutoReply implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public Message $message, public string $locale) {}

    public function handle(): void
    {
        if (blank($this->message->email)) {
            return;
        }

        $mailer = BrevoMailConfigurator::resolveMailer();

        Mail::mailer($mailer)
            ->to($this->message->email)
            ->locale($this->locale)
            ->send(new ContactAutoReplyMail($this->message));
    }

    /**
     * Surface a final send failure (see SendContactReply::failed()).
     */
    public function failed(Throwable $e): void
    {
        report($e);

        Log::error('Contact auto-reply email failed to send', [
            'message_id' => $this->message->id ?? null,
            'recipient' => $this->message->email ?? null,
            'error' => $e->getMessage(),
        ]);
    }
}
