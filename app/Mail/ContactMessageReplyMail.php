<?php

namespace App\Mail;

use App\Models\Message;
use App\Services\BrevoMailConfigurator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Message $message,
        public string $replySubject,
        public string $replyBody,
    ) {}

    public function envelope(): Envelope
    {
        $from = BrevoMailConfigurator::fromAddress();
        $office = BrevoMailConfigurator::officeEmail();

        return new Envelope(
            from: new Address($from['address'], $from['name']),
            // Replies from the visitor (Gmail/Outlook) go to the office inbox,
            // so the conversation continues over normal email — not the CMS.
            replyTo: [new Address($office, $from['name'])],
            subject: $this->replySubject,
        );
    }

    public function content(): Content
    {
        // NOTE: "$message" is reserved inside mail views (the Symfony message),
        // so the model is passed as "contactMessage".
        return new Content(
            view: 'emails.contact.reply',
            with: [
                'contactMessage' => $this->message,
                'replySubject' => $this->replySubject,
                'replyBody' => $this->replyBody,
            ],
        );
    }
}
