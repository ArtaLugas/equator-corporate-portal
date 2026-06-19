<?php

namespace App\Http\Requests;

use App\Rules\Turnstile;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    /**
     * Public endpoint — anyone may submit the contact form.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191', 'not_regex:/[\r\n]/'],
            // `email:filter` (PHP filter_var) rejects CR/LF, mitigating the
            // Laravel 11 "CRLF injection in default email rule" advisory at the
            // application layer (we intentionally stay on Laravel 11). Symfony
            // Mailer also rejects CRLF in addresses as a second line of defence.
            'email' => ['required', 'email:filter', 'max:191', 'not_regex:/[\r\n]/'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:191'],
            'subject' => ['required', 'string', 'max:191'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],

            // Honeypot: must remain empty (bots tend to fill every field).
            'website' => ['prohibited'],

            // Cloudflare Turnstile (CAPTCHA) — wajib & diverifikasi ke server Cloudflare.
            'cf-turnstile-response' => ['required', new Turnstile($this->ip())],
        ];
    }

    public function messages(): array
    {
        return [
            'website.prohibited' => 'Spam detected.',
            'cf-turnstile-response.required' => 'Please complete the security verification first.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'name',
            'email' => 'email address',
            'subject' => 'subject',
            'message' => 'message',
        ];
    }
}
