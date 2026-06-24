<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /** Cached for an hour — search engines do not need second-level freshness. */
    public function index()
    {
        $xml = Cache::remember('public.sitemap.xml', now()->addHour(), fn () => $this->build());

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    /**
     * robots.txt — a controller action (not a route closure) so the route set
     * remains serializable by `route:cache`.
     */
    public function robots()
    {
        $body = implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            '',
            'Sitemap: '.route('sitemap'),
        ])."\n";

        return response($body, 200)->header('Content-Type', 'text/plain');
    }

    private function build(): string
    {
        $locales = array_keys(config('locales.supported', []));
        $default = config('locales.default');

        // Each entry: [routeName, params, lastmod].
        $entries = [];

        foreach (['home', 'about', 'services.index', 'projects.index', 'news.index', 'faq', 'contact', 'privacy', 'cookies'] as $name) {
            $entries[] = [$name, [], null];
        }

        foreach (Service::where('status', 'published')->get(['slug', 'updated_at']) as $s) {
            $entries[] = ['services.show', ['slug' => $s->slug], $s->updated_at];
        }

        foreach (Project::public()->get(['slug', 'updated_at']) as $p) {
            $entries[] = ['projects.show', ['slug' => $p->slug], $p->updated_at];
        }

        foreach (News::where('status', 'published')->get(['slug', 'updated_at']) as $n) {
            $entries[] = ['news.show', ['slug' => $n->slug], $n->updated_at];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach ($entries as [$name, $params, $lastmod]) {
            // Canonical default-locale URL stays unprefixed; others are /{locale}/…
            $urls = [];
            foreach ($locales as $loc) {
                $urls[$loc] = $loc === $default
                    ? route($name, $params)
                    : route($name, ['locale' => $loc] + $params);
            }

            // Shared hreflang alternates (each locale version lists them all).
            $alts = '';
            foreach ($locales as $loc) {
                $alts .= '    <xhtml:link rel="alternate" hreflang="'.$loc.'" href="'.e($urls[$loc]).'"/>'."\n";
            }
            $alts .= '    <xhtml:link rel="alternate" hreflang="x-default" href="'.e($urls[$default]).'"/>'."\n";

            foreach ($locales as $loc) {
                $xml .= '  <url>'."\n";
                $xml .= '    <loc>'.e($urls[$loc]).'</loc>'."\n";
                $xml .= $alts;
                if ($lastmod) {
                    $xml .= '    <lastmod>'.$lastmod->toAtomString().'</lastmod>'."\n";
                }
                $xml .= '  </url>'."\n";
            }
        }

        return $xml.'</urlset>'."\n";
    }
}
