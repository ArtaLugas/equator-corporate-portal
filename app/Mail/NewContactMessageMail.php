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

class NewContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Message $message)
    {
    }

    public function envelope(): Envelope
    {
        $from = BrevoMailConfigurator::fromAddress();

        return new Envelope(
            from: new Address($from['address'], $from['name']),
            // Office can reply directly to the visitor from their inbox.
            replyTo: [new Address($this->message->email, $this->message->name ?? '')],
            subject: 'New Contact Message - ' . ($this->message->subject ?: 'No subject'),
        );
    }

    public function content(): Content
    {
        // NOTE: "$message" is reserved inside mail views (the Symfony message),
        // so the model is passed as "contactMessage".
        return new Content(
            view: 'emails.contact.new-message',
            with: ['contactMessage' => $this->message],
        );
    }
}
