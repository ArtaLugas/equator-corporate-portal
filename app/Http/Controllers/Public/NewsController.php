<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $categories = NewsCategory::withCount(['news' => fn ($q) => $q->published()])
            ->orderBy('name')->get();

        $activeCategory = $request->filled('category')
            ? $categories->firstWhere('slug', $request->category)
            : null;

        $search = trim((string) $request->search);
        $hasFilter = $activeCategory || $search !== '';

        // Editorial lead — newest article, only on the unfiltered view; excluded from the grid.
        $lead = ! $hasFilter
            ? News::published()->with('category')->latest('published_at')->first()
            : null;

        $news = News::published()
            ->with('category')
            ->when($activeCategory, fn ($q) => $q->where('category_id', $activeCategory->id))
            ->when($search !== '', fn ($q) => $q->where(function ($w) use ($search) {
                foreach (array_keys(config('locales.supported', [])) as $locale) {
                    $w->orWhere("title_{$locale}", 'like', "%{$search}%");
                }
            }))
            ->when($lead, fn ($q) => $q->where('id', '!=', $lead->id))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        // Most Read — rendered only when real view data exists (no dummy).
        $mostRead = News::published()->where('views_count', '>', 0)
            ->with('category')->orderByDesc('views_count')->take(5)->get();

        return view('public.news.index', compact('news', 'categories', 'activeCategory', 'lead', 'mostRead', 'search'));
    }

    public function show(Request $request, string $slug)
    {
        $article = News::published()
            ->where('slug', $slug)
            ->with(['category', 'tags'])
            ->firstOrFail();

        // Count one view per session per article, skipping obvious bots — keeps
        // the "Most Read" ranking representative of real readers (see recordView).
        $this->recordView($request, $article);

        // Sidebar — latest articles (excluding current).
        $recent = News::published()
            ->where('id', '!=', $article->id)
            ->with('category')
            ->latest('published_at')->take(5)->get();

        // Sidebar — all categories with published counts (active highlighted in view).
        $categories = NewsCategory::withCount(['news' => fn ($q) => $q->published()])
            ->orderBy('name')->get();

        return view('public.news.show', compact('article', 'recent', 'categories'));
    }

    /**
     * Increment views_count at most once per session per article, and never for
     * obvious bots/crawlers. Uses the session that already exists for CSRF — no
     * new tracking cookie (keeps the consent surface unchanged). The increment
     * still avoids touching updated_at.
     */
    private function recordView(Request $request, News $article): void
    {
        if ($this->isLikelyBot($request)) {
            return;
        }

        $seen = (array) $request->session()->get('news_viewed', []);

        if (in_array($article->id, $seen, true)) {
            return;
        }

        $seen[] = $article->id;
        $request->session()->put('news_viewed', $seen);

        News::where('id', $article->id)->increment('views_count');
    }

    /**
     * Heuristic bot filter for view counting only (NOT a security control).
     * An empty User-Agent is treated as a bot, since real browsers always send one.
     */
    private function isLikelyBot(Request $request): bool
    {
        $ua = (string) $request->userAgent();

        if ($ua === '') {
            return true;
        }

        return (bool) preg_match(
            '/bot|crawl|spider|slurp|mediapartners|facebookexternalhit|embedly|quora|pinterest|slackbot|telegrambot|bitlybot|monitor|uptime|curl|wget|python-requests|headless|phantomjs/i',
            $ua
        );
    }
}
