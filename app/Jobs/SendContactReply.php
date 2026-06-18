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
use Illuminate\Support\Facades\Mail;

class SendContactReply implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public Message $message,
        public string $subject,
        public string $body,
    ) {
    }

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
}
