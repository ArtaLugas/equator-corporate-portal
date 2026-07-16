<?php

namespace App\Jobs;

use App\Mail\ContactMessageReplyMail;
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

class SendContactReply implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public Message $message,
        public string $subject,
        public string $body,
    ) {}

    public function handle(): void
    {
        $mailer = BrevoMailConfigurator::resolveMailer();

        Mail::mailer($mailer)
            ->to($this->message->email, $this->message->name ?? '')
            ->send(new ContactMessageReplyMail(
                $this->message,
                $this->subject,
                $this->body,
            ));
    }

    /**
     * Runs after the final retry fails: surface the failure instead of letting
     * it die silently in the failed_jobs table (the admin CMS shows a health
     * alert; report() routes to the configured log/Slack channels).
     */
    public function failed(Throwable $e): void
    {
        report($e);

        Log::error('Contact reply email failed to send', [
            'message_id' => $this->message->id ?? null,
            'recipient' => $this->message->email ?? null,
            'error' => $e->getMessage(),
        ]);
    }
}
