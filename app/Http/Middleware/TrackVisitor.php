<?php

namespace App\Http\Middleware;

use App\Models\Visitor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    /**
     * Record a public page view for visitor analytics.
     *
     * Skips: non-GET requests, admin panel, and asset/util paths.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            if ($this->shouldTrack($request, $response)) {
                Visitor::create([
                    'ip_address' => $request->ip(),
                    'url' => mb_substr($request->fullUrl(), 0, 500),
                    'referer' => mb_substr((string) $request->headers->get('referer'), 0, 500) ?: null,
                    'user_agent' => $request->userAgent(),
                    'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                    'visited_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Never let analytics break the request.
            report($e);
        }

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        // Only count successful HTML page views.
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        // Skip the admin panel and infra/asset paths.
        if ($request->is('admin', 'admin/*', 'up', 'build/*', 'storage/*', 'favicon.ico', 'robots.txt', 'sitemap.xml')) {
            return false;
        }

        // Skip AJAX / JSON requests.
        if ($request->ajax() || $request->wantsJson()) {
            return false;
        }

        return true;
    }
}
