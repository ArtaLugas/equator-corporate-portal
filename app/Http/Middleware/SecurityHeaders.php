<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Attach baseline OWASP-recommended security response headers.
     *
     * Notes:
     * - HSTS is emitted here but ONLY over HTTPS ($request->secure()), so it never
     *   poisons local/plain-HTTP development. Setting it in the app (rather than the
     *   web server) keeps it portable across shared hosting (cPanel/Apache/LiteSpeed)
     *   where we do not control a vhost.
     * - A full Content-Security-Policy is deferred: the admin panel loads CKEditor 5,
     *   ApexCharts and inline dashboard scripts, so CSP must be tuned (report-only
     *   first) so it does not break them. The headers below are the safe baseline.
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

        // HSTS only makes sense — and is only safe — over HTTPS.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
