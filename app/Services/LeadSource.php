<?php

namespace App\Services;

use Illuminate\Http\Request;

/**
 * Captures lead-source attribution automatically — the visitor never fills it.
 *
 * Strategy: FIRST-TOUCH. On the visitor's first public page view we snapshot the
 * landing page, external referrer, UTM params and ad click ids into the session
 * (which already exists for CSRF). When they later submit the contact form, that
 * snapshot is attached to the Message — so a lead that arrived via an ad campaign
 * is still attributed even after browsing several pages.
 */
class LeadSource
{
    public const SESSION_KEY = 'lead_source';

    private const UTM = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

    /** Record first-touch attribution once per session (called from middleware). */
    public function capture(Request $request): void
    {
        if (! $request->hasSession() || $request->session()->has(self::SESSION_KEY)) {
            return;
        }

        $data = [
            'landing_page' => $this->clip($request->fullUrl(), 500),
            'referrer' => $this->externalReferrer($request),
            'gclid' => $this->clip($this->queryString($request, 'gclid'), 255),
            'fbclid' => $this->clip($this->queryString($request, 'fbclid'), 255),
        ];

        foreach (self::UTM as $key) {
            $data[$key] = $this->clip($this->queryString($request, $key), 191);
        }

        $request->session()->put(self::SESSION_KEY, $data);
    }

    /** Assemble the full metadata to persist on a new Message. */
    public function metadata(Request $request): array
    {
        $s = $request->hasSession() ? (array) $request->session()->get(self::SESSION_KEY, []) : [];

        return [
            'landing_page' => $s['landing_page'] ?? $this->clip($request->fullUrl(), 500),
            'referrer' => $s['referrer'] ?? $this->externalReferrer($request),
            'locale' => app()->getLocale(),
            'utm_source' => $s['utm_source'] ?? null,
            'utm_medium' => $s['utm_medium'] ?? null,
            'utm_campaign' => $s['utm_campaign'] ?? null,
            'utm_content' => $s['utm_content'] ?? null,
            'utm_term' => $s['utm_term'] ?? null,
            'gclid' => $s['gclid'] ?? null,
            'fbclid' => $s['fbclid'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $this->clip((string) $request->userAgent(), 500),
        ];
    }

    /** Referrer only when it comes from another site (ignore internal navigation). */
    private function externalReferrer(Request $request): ?string
    {
        $ref = $request->headers->get('referer');
        if (blank($ref)) {
            return null;
        }

        $host = parse_url($ref, PHP_URL_HOST);
        if ($host && strcasecmp($host, $request->getHost()) === 0) {
            return null;
        }

        return $this->clip($ref, 500);
    }

    /** Read a query param defensively (ignore array/non-string input). */
    private function queryString(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : null;
    }

    private function clip(?string $value, int $max): ?string
    {
        $value = $value !== null ? trim($value) : null;

        return ($value === null || $value === '') ? null : mb_substr($value, 0, $max);
    }
}
