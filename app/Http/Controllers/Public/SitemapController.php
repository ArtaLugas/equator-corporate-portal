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

    private function build(): string
    {
        $urls = collect();

        foreach (['home', 'about', 'services.index', 'projects.index', 'news.index', 'faq', 'contact'] as $name) {
            $urls->push(['loc' => route($name), 'lastmod' => null]);
        }

        Service::where('status', 'published')->get(['slug', 'updated_at'])
            ->each(fn ($s) => $urls->push(['loc' => route('services.show', $s->slug), 'lastmod' => $s->updated_at]));

        Project::get(['slug', 'updated_at'])
            ->each(fn ($p) => $urls->push(['loc' => route('projects.show', $p->slug), 'lastmod' => $p->updated_at]));

        News::where('status', 'published')->get(['slug', 'updated_at'])
            ->each(fn ($n) => $urls->push(['loc' => route('news.show', $n->slug), 'lastmod' => $n->updated_at]));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $u) {
            $xml .= '  <url><loc>'.e($u['loc']).'</loc>';
            if ($u['lastmod']) {
                $xml .= '<lastmod>'.$u['lastmod']->toAtomString().'</lastmod>';
            }
            $xml .= '</url>'."\n";
        }

        return $xml.'</urlset>'."\n";
    }
}
