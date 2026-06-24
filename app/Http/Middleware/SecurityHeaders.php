<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Baseline Content-Security-Policy.
     *
     * `'unsafe-inline'`/`'unsafe-eval'` are required because the app uses Alpine.js
     * (Function-constructor) plus inline <script>/<style> blocks across views — so this
     * is a *defence-in-depth* policy (the primary XSS defence is purifying all rich
     * text at the source). It still meaningfully locks down object/base/form-action,
     * blocks framing (clickjacking) and restricts where scripts/styles/images may load.
     *
     * Allowlisted third parties:
     * - challenges.cloudflare.com  → Turnstile CAPTCHA (script + iframe) on contact/login
     * - cdn.jsdelivr.net           → Alpine.js on the admin login screen
     * - googletagmanager.com       → Google Analytics 4 (gtag.js) — consent-gated
     * - *.google-analytics.com     → GA4 measurement / collect — consent-gated
     * - data:/blob:                → inline icons + CKEditor image preview/workers
     *
     * If a future integration needs another origin, extend this constant (kept here,
     * not in env(), so it stays correct under `config:cache`).
     */
    private const POLICY = "default-src 'self'; "
        ."script-src 'self' 'unsafe-inline' 'unsafe-eval' https://challenges.cloudflare.com https://cdn.jsdelivr.net https://www.googletagmanager.com; "
        ."style-src 'self' 'unsafe-inline'; "
        ."img-src 'self' data: blob: https://www.google-analytics.com https://*.google-analytics.com; "
        ."font-src 'self' data:; "
        ."connect-src 'self' https://challenges.cloudflare.com https://www.google-analytics.com https://*.google-analytics.com https://*.analytics.google.com; "
        .'frame-src https://challenges.cloudflare.com; '
        ."worker-src 'self' blob:; "
        ."object-src 'none'; "
        ."base-uri 'self'; "
        ."form-action 'self'; "
        ."frame-ancestors 'self'";

    /**
     * Attach OWASP-recommended security response headers.
     *
     * HSTS is emitted only over HTTPS ($request->secure()) so it never poisons
     * local/plain-HTTP development, and lives in the app (not a vhost) so it stays
     * portable across shared hosting (cPanel/Apache/LiteSpeed).
     *
     * @see https://owasp.org/www-project-secure-headers/
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->add([
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
            'X-Permitted-Cross-Domain-Policies' => 'none',
        ]);

        // Enforce CSP in production only. Local development serves assets from the
        // cross-origin Vite dev server (http://…:5173 + HMR websocket), which a
        // `default-src 'self'` policy would block; production serves same-origin
        // built assets, so the policy applies cleanly there.
        if (app()->isProduction()) {
            $response->headers->set('Content-Security-Policy', self::POLICY);
        }

        // HSTS only makes sense — and is only safe — over HTTPS.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
