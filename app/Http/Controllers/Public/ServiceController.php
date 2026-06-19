<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServiceController extends Controller
{
    /** Cache key for the stable index chrome (category rail + total). */
    public const META_CACHE_KEY = 'public.services.meta';

    public function index(Request $request)
    {
        // The category rail + total rarely change; cache them (busted by the
        // content observer). The search/filter/paginated list stays dynamic.
        [$categories, $totalServices] = Cache::remember(self::META_CACHE_KEY, now()->addHour(), function () {
            $categories = ServiceCategory::where('status', 'active')
                ->withCount(['services' => fn ($q) => $q->where('status', 'published')])
                ->orderBy('display_order')->get();

            return [$categories, Service::where('status', 'published')->count()];
        });

        $activeCategory = $request->filled('category')
            ? $categories->firstWhere('slug', $request->category)
            : null;

        $hasSearch = $request->filled('search');

        // Spotlight: featured services only on the unfiltered "All" view (no category, no search).
        // Excluded from the main index below so a service never appears twice on the same screen.
        $showcase = ! $activeCategory && ! $hasSearch;

        $featured = $showcase
            ? Service::where('status', 'published')
                ->where('is_featured', true)
                ->with('category')
                ->latest()
                ->take(3)->get()
            : collect();

        $services = Service::where('status', 'published')
            ->with('category')
            ->when($activeCategory, fn ($q) => $q->where('category_id', $activeCategory->id))
            ->when($hasSearch, fn ($q) => $q->where('name', 'like', '%'.trim($request->search).'%'))
            ->when($featured->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $featured->pluck('id')))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('public.services.index', compact(
            'categories', 'services', 'activeCategory', 'featured', 'totalServices'
        ));
    }

    public function show(string $slug)
    {
        $service = Service::where('status', 'published')
            ->where('slug', $slug)
            ->with([
                'category',
                // Real project references (case studies) linked to this service.
                'projects' => fn ($q) => $q->public()->latest()->take(3),
            ])
            ->firstOrFail();

        return view('public.services.show', compact('service'));
    }
}
