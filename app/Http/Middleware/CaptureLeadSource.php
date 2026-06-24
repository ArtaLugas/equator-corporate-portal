<?php

namespace App\Http\Middleware;

use App\Services\LeadSource;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records first-touch lead-source attribution into the session on public page
 * views, so the contact form can attribute the lead without the visitor typing
 * anything. Cheap: writes to the session only once per visitor.
 */
class CaptureLeadSource
{
    public function __construct(private readonly LeadSource $leadSource) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET')
            && ! $request->is('admin', 'admin/*', 'up', 'build/*', 'storage/*', 'sitemap.xml', 'robots.txt')
            && ! $request->ajax()
            && ! $request->wantsJson()
        ) {
            try {
                $this->leadSource->capture($request);
            } catch (\Throwable $e) {
                // Lead attribution is non-critical — it must never break a public
                // page render (same defensive contract as TrackVisitor).
                report($e);
            }
        }

        return $next($request);
    }
}
