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
        $categories = NewsCategory::withCount(['news' => fn ($q) => $q->where('status', 'published')])
            ->orderBy('name')->get();

        $activeCategory = $request->filled('category')
            ? $categories->firstWhere('slug', $request->category)
            : null;

        $search = trim((string) $request->search);
        $hasFilter = $activeCategory || $search !== '';

        // Editorial lead — newest article, only on the unfiltered view; excluded from the grid.
        $lead = ! $hasFilter
            ? News::where('status', 'published')->with('category')->latest('published_at')->first()
            : null;

        $news = News::where('status', 'published')
            ->with('category')
            ->when($activeCategory, fn ($q) => $q->where('category_id', $activeCategory->id))
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->when($lead, fn ($q) => $q->where('id', '!=', $lead->id))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        // Most Read — rendered only when real view data exists (no dummy).
        $mostRead = News::where('status', 'published')->where('views_count', '>', 0)
            ->with('category')->orderByDesc('views_count')->take(5)->get();

        return view('public.news.index', compact('news', 'categories', 'activeCategory', 'lead', 'mostRead', 'search'));
    }

    public function show(string $slug)
    {
        $article = News::where('status', 'published')
            ->where('slug', $slug)
            ->with(['category', 'tags'])
            ->firstOrFail();

        // Increment view counter (without touching updated_at).
        News::where('id', $article->id)->increment('views_count');

        // Sidebar — latest articles (excluding current).
        $recent = News::where('status', 'published')
            ->where('id', '!=', $article->id)
            ->with('category')
            ->latest('published_at')->take(5)->get();

        // Sidebar — all categories with published counts (active highlighted in view).
        $categories = NewsCategory::withCount(['news' => fn ($q) => $q->where('status', 'published')])
            ->orderBy('name')->get();

        return view('public.news.show', compact('article', 'recent', 'categories'));
    }
}
