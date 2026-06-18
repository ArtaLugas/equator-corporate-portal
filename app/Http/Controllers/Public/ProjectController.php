<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $countries = Project::whereNotNull('country')->distinct()->orderBy('country')->pluck('country');

        $projects = Project::query()
            ->with('services')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('country'), fn ($q) => $q->where('country', $request->country))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim($request->search);
                $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")
                    ->orWhere('client_name', 'like', "%{$term}%")
                    ->orWhere('location', 'like', "%{$term}%"));
            })
            ->orderByDesc('is_featured')->latest()
            ->paginate(9)
            ->withQueryString();

        return view('public.projects.index', compact('projects', 'countries'));
    }

    public function show(string $slug)
    {
        $project = Project::where('slug', $slug)
            ->with(['services', 'images' => fn ($q) => $q->orderBy('display_order')])
            ->firstOrFail();

        // Project terkait = berbagi minimal satu service yang sama.
        $serviceIds = $project->services->pluck('id');

        $related = Project::where('id', '!=', $project->id)
            ->when($serviceIds->isNotEmpty(), fn ($q) => $q->whereHas(
                'services',
                fn ($s) => $s->whereIn('services.id', $serviceIds)
            ))
            ->with('services')
            ->latest()->take(3)->get();

        return view('public.projects.show', compact('project', 'related'));
    }
}
