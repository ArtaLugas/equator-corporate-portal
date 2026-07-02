<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

/**
 * Sends mail through Brevo's transactional HTTP API (https, port 443) instead
 * of SMTP. Shared hosting (e.g. cPanel/LiteSpeed) frequently blocks or
 * transparently redirects outbound SMTP (25/465/587/2525) to its own mail
 * server, which breaks the Brevo SMTP relay with a TLS certificate mismatch.
 * HTTPS cannot be blocked without taking the whole site down, so this path is
 * resilient on restrictive hosts while still delivering through Brevo.
 *
 * Wired up via Mail::extend('brevo-api', …) in AppServiceProvider; selected by
 * App\Services\BrevoMailConfigurator when a BREVO_API_KEY is configured.
 */
class BrevoApiTransport extends AbstractTransport
{
    private const ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

    public function __construct(private string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $payload = array_filter([
            'sender' => $this->address($email->getFrom()[0] ?? null),
            'to' => $this->addresses($email->getTo()),
            'cc' => $this->addresses($email->getCc()),
            'bcc' => $this->addresses($email->getBcc()),
            'replyTo' => $this->address($email->getReplyTo()[0] ?? null),
            'subject' => $email->getSubject(),
            'htmlContent' => $this->body($email->getHtmlBody()),
            'textContent' => $this->body($email->getTextBody()),
            'attachment' => $this->attachments($email),
        ], fn ($value) => $value !== null && $value !== []);

        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
        ])->timeout(20)->asJson()->post(self::ENDPOINT, $payload);

        // Throw on failure so the queued job can retry and the failure is logged
        // — the contact controller already shields the visitor from 5xx.
        if ($response->failed()) {
            throw new \RuntimeException(
                'Brevo API send failed ('.$response->status().'): '.$response->body()
            );
        }
    }

    /** @return array{email: string, name?: string}|null */
    private function address(?Address $address): ?array
    {
        if (! $address) {
            return null;
        }

        return array_filter([
            'email' => $address->getAddress(),
            'name' => $address->getName() ?: null,
        ], fn ($value) => $value !== null);
    }

    /** @param  Address[]  $addresses */
    private function addresses(array $addresses): array
    {
        return array_values(array_filter(array_map(
            fn (Address $address) => $this->address($address),
            $addresses
        )));
    }

    private function attachments(Email $email): array
    {
        $out = [];

        foreach ($email->getAttachments() as $attachment) {
            $headers = $attachment->getPreparedHeaders();
            $name = $headers->getHeaderParameter('content-disposition', 'filename') ?: 'attachment';

            $out[] = [
                'name' => $name,
                'content' => base64_encode($attachment->getBody()),
            ];
        }

        return $out;
    }

    /** Body parts may be a string or a stream resource — normalise to string. */
    private function body(mixed $body): ?string
    {
        if ($body === null) {
            return null;
        }

        return is_resource($body) ? stream_get_contents($body) : (string) $body;
    }

    public function __toString(): string
    {
        return 'brevo-api';
    }
}
