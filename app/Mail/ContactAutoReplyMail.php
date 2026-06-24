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

/**
 * Confirmation ("auto-reply") sent to the visitor after they submit the contact
 * form. Rendered in the locale set by the dispatching job (->locale($locale)),
 * so the subject and body follow the website's active language.
 */
class ContactAutoReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Message $message) {}

    public function envelope(): Envelope
    {
        $from = BrevoMailConfigurator::fromAddress();

        return new Envelope(
            from: new Address($from['address'], $from['name']),
            subject: __('emails.auto_reply.subject', ['reference' => $this->message->reference]),
        );
    }

    public function content(): Content
    {
        // "$message" is reserved inside mail views (the Symfony message), so the
        // model is passed as "contactMessage" — same convention as the other mails.
        return new Content(
            view: 'emails.contact.auto-reply',
            with: ['contactMessage' => $this->message],
        );
    }
}
