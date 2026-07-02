<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ProjectController extends Controller
{
    /** Cache key for the stable index chrome (stats + facets). */
    public const CHROME_CACHE_KEY = 'public.projects.chrome';

    public function index(Request $request)
    {
        // Stats + discovery facets are stable across requests — cache them
        // (busted by the content observer). The filtered list stays dynamic.
        // All counts use scopePublic() so figures match what visitors can open.
        [$stats, $countries, $years, $serviceGroups] = Cache::remember(
            self::CHROME_CACHE_KEY,
            now()->addHour(),
            function () {
                $minStart = Project::public()->whereNotNull('start_date')->min('start_date');

                return [
                    [
                        'total' => Project::public()->count(),
                        'completed' => Project::public()->where('status', 'completed')->count(),
                        'clients' => Project::public()->whereNotNull('client_name')->distinct()->count('client_name'),
                        'since' => $minStart ? Carbon::parse($minStart)->year : null,
                    ],
                    Project::public()->whereNotNull('country')->distinct()->orderBy('country')->pluck('country'),
                    Project::public()->whereNotNull('start_date')
                        ->selectRaw('YEAR(start_date) as y')->distinct()->orderByDesc('y')->pluck('y'),
                    ServiceCategory::where('status', 'active')
                        ->whereHas('services', fn ($q) => $q->where('status', 'published'))
                        ->with(['services' => fn ($q) => $q->where('status', 'published')->orderBy('name')])
                        ->orderBy('display_order')->get(),
                ];
            }
        );

        $activeService = $request->filled('service')
            ? Service::where('status', 'published')->where('slug', $request->service)->first()
            : null;

        // Featured projects are pinned to the top; the rest fall back to most recent first.
        $projects = Project::query()->public()
            ->with('services')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('country'), fn ($q) => $q->where('country', $request->country))
            ->when($request->filled('year'), fn ($q) => $q->whereYear('start_date', $request->year))
            ->when($activeService, fn ($q) => $q->whereHas(
                'services', fn ($s) => $s->where('services.id', $activeService->id)
            ))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim($request->search);
                $q->where(function ($w) use ($term) {
                    foreach (array_keys(config('locales.supported', [])) as $locale) {
                        $w->orWhere("name_{$locale}", 'like', "%{$term}%");
                    }
                    $w->orWhere('client_name', 'like', "%{$term}%")
                        ->orWhere('location', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('is_featured')
            ->latest('start_date')->latest()
            ->paginate(9)
            ->withQueryString();

        return view('public.projects.index', compact(
            'projects', 'countries', 'years', 'stats', 'serviceGroups', 'activeService'
        ));
    }

    public function show(string $slug)
    {
        $project = Project::public()->where('slug', $slug)
            ->with(['services', 'images' => fn ($q) => $q->orderBy('display_order')])
            ->firstOrFail();

        $serviceIds = $project->services->pluck('id');

        // 1) Primary relation — projects that share ≥1 service (true "related").
        $related = collect();
        $relatedByService = false;

        if ($serviceIds->isNotEmpty()) {
            $related = Project::public()->where('id', '!=', $project->id)
                ->whereHas('services', fn ($s) => $s->whereIn('services.id', $serviceIds))
                ->with('services')
                ->latest('start_date')->take(3)->get();
            $relatedByService = $related->isNotEmpty();
        }

        // 2) Graceful fallback chain to top up to 3: same country → same status → latest.
        $fillers = [];
        if ($project->country) {
            $fillers[] = fn ($q) => $q->where('country', $project->country);
        }
        $fillers[] = fn ($q) => $q->where('status', $project->status);
        $fillers[] = fn ($q) => $q;

        foreach ($fillers as $filler) {
            if ($related->count() >= 3) {
                break;
            }
            $exclude = $related->pluck('id')->push($project->id)->all();
            $rows = $filler(Project::query()->public())
                ->whereNotIn('id', $exclude)
                ->with('services')
                ->latest('start_date')
                ->take(3 - $related->count())->get();
            $related = $related->concat($rows);
        }

        return view('public.projects.show', compact('project', 'related', 'relatedByService'));
    }
}
