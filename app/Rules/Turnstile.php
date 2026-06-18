<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

/**
 * Memverifikasi token Cloudflare Turnstile ke endpoint siteverify.
 *
 * Dipakai sebagai aturan validasi: ['required', new Turnstile($request->ip())]
 */
class Turnstile implements ValidationRule
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct(private ?string $ip = null)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secret = config('services.turnstile.secret_key');

        // Mode "selalu wajib": tanpa secret key, verifikasi dianggap gagal.
        if (blank($secret)) {
            $fail('Security verification is not configured. Please contact the administrator.');

            return;
        }

        if (blank($value) || ! is_string($value)) {
            $fail('Please complete the security verification first.');

            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post(self::VERIFY_URL, array_filter([
                    'secret' => $secret,
                    'response' => $value,
                    'remoteip' => $this->ip,
                ]));

            $success = $response->ok() && $response->json('success') === true;
        } catch (\Throwable $e) {
            report($e);
            $success = false;
        }

        if (! $success) {
            $fail('Security verification failed. Please try again.');
        }
    }
}
