<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Tag;
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

        $activeTag = $request->filled('tag') ? Tag::where('slug', $request->tag)->first() : null;

        $news = News::where('status', 'published')
            ->with(['category', 'tags'])
            ->when($activeCategory, fn ($q) => $q->where('category_id', $activeCategory->id))
            ->when($activeTag, fn ($q) => $q->whereHas('tags', fn ($t) => $t->where('tags.id', $activeTag->id)))
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%' . trim($request->search) . '%'))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        $popularTags = Tag::has('news')->take(15)->get();

        return view('public.news.index', compact('news', 'categories', 'activeCategory', 'activeTag', 'popularTags'));
    }

    public function show(string $slug)
    {
        $article = News::where('status', 'published')
            ->where('slug', $slug)
            ->with(['category', 'tags'])
            ->firstOrFail();

        // Increment view counter (without touching updated_at).
        News::where('id', $article->id)->increment('views_count');

        $recent = News::where('status', 'published')
            ->where('id', '!=', $article->id)
            ->latest('published_at')->take(4)->get();

        return view('public.news.show', compact('article', 'recent'));
    }
}
