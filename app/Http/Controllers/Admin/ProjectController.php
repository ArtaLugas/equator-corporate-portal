<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\GeneratesUniqueSlug;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectRequest;
use App\Models\Project;
use App\Models\Service;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    use GeneratesUniqueSlug;

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    private const PAGINATION = 10;

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Project::query()
            ->with(['services' => fn ($q) => $q->withTrashed()])
            ->withCount('images');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                // Translatable name across every locale column, plus the slug,
                // status, and the project's own facets (client / location).
                foreach (array_keys(config('locales.supported', [])) as $locale) {
                    $q->orWhere("name_{$locale}", 'like', "%{$search}%");
                }

                $q->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Service Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('service')) {

            $query->whereHas(
                'services',
                fn ($q) => $q->where('services.id', $request->service)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Country Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('country')) {

            $query->where(
                'country',
                $request->country
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Featured Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('featured')) {

            $query->where(
                'is_featured',
                $request->featured
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        switch ($request->sort) {

            case 'oldest':

                // Order by the project's start date (matches the public site),
                // with created_at as the tiebreaker for rows sharing a date / null.
                $query->oldest('start_date')->oldest();

                break;

            case 'name_asc':

                $query->orderBy('name_'.config('locales.default'));

                break;

            case 'name_desc':

                $query->orderByDesc('name_'.config('locales.default'));

                break;

            default:

                // "Newest": most recent project start date first, mirroring the
                // public listing (featured pinning is a public-only concern).
                $query->latest('start_date')->latest();

                break;
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $projects = $query
            ->paginate(self::PAGINATION)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Country Options (for filter dropdown)
        |--------------------------------------------------------------------------
        */

        $countries = Project::query()
            ->whereNotNull('country')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        $services = Service::orderBy('name')->get();

        return view(
            'admin.projects.index',
            compact(
                'projects',
                'countries',
                'services'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $services = Service::orderBy('name')->get();

        return view('admin.projects.create', compact('services'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(ProjectRequest $request)
    {
        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Boolean Normalize
        |--------------------------------------------------------------------------
        */

        $validated['is_featured'] = $request->boolean('is_featured');

        /*
        |--------------------------------------------------------------------------
        | Generate Slug (from the default-locale name)
        |--------------------------------------------------------------------------
        */

        $defaultName = $validated['name_'.config('locales.default')];

        $validated['slug'] = $this->generateUniqueSlug(Project::class, $defaultName);

        $featuredImage = null;

        $galleryPaths = [];

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Upload Featured Image
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('featured_image')) {

                $featuredImage = $this->uploadImage(
                    $request->file('featured_image'),
                    'projects',
                    $defaultName
                );

                $validated['featured_image'] = $featuredImage;
            }

            /*
            |--------------------------------------------------------------------------
            | Create Project
            |--------------------------------------------------------------------------
            */

            $project = Project::create($validated);

            // Sinkronkan service many-to-many.
            $project->services()->sync($validated['service_ids']);

            /*
            |--------------------------------------------------------------------------
            | Upload Gallery Images
            |--------------------------------------------------------------------------
            */

            $galleryPaths = $this->storeGalleryImages(
                $project,
                $request->file('gallery_images', [])
            );

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(
                'Project',
                'Created project: '.$project->name
            );

            DB::commit();

            return redirect()
                ->route('admin.projects.index')
                ->with('success', 'Project created successfully.');

        } catch (\Throwable $e) {

            DB::rollBack();

            /*
            |--------------------------------------------------------------------------
            | Cleanup Uploaded Files
            |--------------------------------------------------------------------------
            */

            $this->deleteFiles(array_merge(
                $featuredImage ? [$featuredImage] : [],
                $galleryPaths
            ));

            report($e);

            return back()
                ->withInput()
                ->with('error', friendly_error($e));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(Project $project)
    {
        $project->load([
            'services' => fn ($q) => $q->withTrashed(),
            'images' => fn ($q) => $q->orderBy('display_order'),
        ]);

        return view(
            'admin.projects.show',
            compact('project')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(Project $project)
    {
        $project->load([
            'services',
            'images' => fn ($q) => $q->orderBy('display_order'),
        ]);

        $services = Service::orderBy('name')->get();

        return view(
            'admin.projects.edit',
            compact('project', 'services')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(ProjectRequest $request, Project $project)
    {
        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Boolean Normalize
        |--------------------------------------------------------------------------
        */

        $validated['is_featured'] = $request->boolean('is_featured');

        /*
        |--------------------------------------------------------------------------
        | Preserve Slug (regenerate only when enabled AND name_en changed)
        |--------------------------------------------------------------------------
        */

        $defaultLocale = config('locales.default');

        $defaultName = $validated['name_'.$defaultLocale];

        if (
            config('cms.auto_regenerate_slug', true)
            && $project->{'name_'.$defaultLocale} !== $defaultName
        ) {

            $validated['slug'] = $this->generateUniqueSlug(
                Project::class,
                $defaultName,
                $project->id
            );
        }

        $oldFeatured = $project->featured_image;

        $newFeatured = null;

        $galleryPaths = [];

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Upload New Featured Image
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('featured_image')) {

                $newFeatured = $this->uploadImage(
                    $request->file('featured_image'),
                    'projects',
                    $defaultName
                );

                $validated['featured_image'] = $newFeatured;
            }

            /*
            |--------------------------------------------------------------------------
            | Remove Existing Featured Image
            |--------------------------------------------------------------------------
            */

            if (! $request->hasFile('featured_image') && $request->boolean('remove_image')) {
                $validated['featured_image'] = null;
            }

            /*
            |--------------------------------------------------------------------------
            | Update Project
            |--------------------------------------------------------------------------
            */

            $project->update($validated);

            // Sinkronkan service many-to-many.
            $project->services()->sync($validated['service_ids']);

            /*
            |--------------------------------------------------------------------------
            | Sync Existing Gallery (caption / order / delete)
            |--------------------------------------------------------------------------
            */

            $removedGallery = $this->syncExistingGallery($project, $request);

            /*
            |--------------------------------------------------------------------------
            | Upload New Gallery Images
            |--------------------------------------------------------------------------
            */

            $galleryPaths = $this->storeGalleryImages(
                $project,
                $request->file('gallery_images', [])
            );

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Cleanup Replaced / Removed Featured Image
            |--------------------------------------------------------------------------
            */

            if ($oldFeatured && ($newFeatured || $request->boolean('remove_image'))) {
                $this->deleteFiles([$oldFeatured]);
            }

            /*
            |--------------------------------------------------------------------------
            | Cleanup Deleted Gallery Files
            |--------------------------------------------------------------------------
            */

            $this->deleteFiles($removedGallery);

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(
                'Project',
                'Updated project: '.$project->name
            );

            // Return to the exact list page the admin came from (preserves the
            // pagination page + filters); guarded so it can't be an open redirect.
            return redirect()
                ->to(guarded_list_url($request->input('return_url'), route('admin.projects.index')))
                ->with('success', 'Project updated successfully.');

        } catch (\Throwable $e) {

            DB::rollBack();

            /*
            |--------------------------------------------------------------------------
            | Cleanup Uploaded Files
            |--------------------------------------------------------------------------
            */

            $this->deleteFiles(array_merge(
                $newFeatured ? [$newFeatured] : [],
                $galleryPaths
            ));

            report($e);

            return back()
                ->withInput()
                ->with('error', friendly_error($e));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY (soft delete)
    |--------------------------------------------------------------------------
    */

    public function destroy(Project $project)
    {
        try {

            DB::transaction(function () use ($project) {
                $project->delete();
            });

            activity_log(
                'Project',
                'Moved project to trash: '.$project->name
            );

            return back()->with('success', 'Project moved to trash.');

        } catch (\Throwable $e) {

            report($e);

            return back()->with('error', friendly_error($e));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | BULK DESTROY (soft delete)
    |--------------------------------------------------------------------------
    */

    public function bulkDestroy()
    {
        $ids = array_map('intval', (array) request()->input('ids', []));

        if (empty($ids)) {
            return back()->with('error', __('flash.none_selected'));
        }

        $count = Project::whereIn('id', $ids)->get()->each->delete()->count();

        activity_log('Project', "Bulk moved {$count} project(s) to trash.");

        return back()->with('success', "{$count} project(s) moved to trash.");
    }

    /*
    |--------------------------------------------------------------------------
    | TRASH
    |--------------------------------------------------------------------------
    */

    public function trash()
    {
        $projects = Project::onlyTrashed()
            ->latest('deleted_at')
            ->paginate(self::PAGINATION);

        return view(
            'admin.projects.trash',
            compact('projects')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESTORE
    |--------------------------------------------------------------------------
    */

    public function restore(int $id)
    {
        try {

            $project = DB::transaction(function () use ($id) {

                $project = Project::onlyTrashed()->findOrFail($id);

                $project->restore();

                return $project;
            });

            activity_log(
                'Project',
                'Restored project: '.$project->name
            );

            return back()->with('success', 'Project restored successfully.');

        } catch (\Throwable $e) {

            report($e);

            return back()->with('error', friendly_error($e));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FORCE DELETE
    |--------------------------------------------------------------------------
    */

    public function forceDelete(int $id)
    {
        $files = [];

        $name = null;

        try {

            DB::transaction(function () use ($id, &$files, &$name) {

                $project = Project::onlyTrashed()
                    ->with('images')
                    ->findOrFail($id);

                /*
                |--------------------------------------------------------------
                | Collect all files (featured + gallery)
                |--------------------------------------------------------------
                */

                if ($project->featured_image) {
                    $files[] = $project->featured_image;
                }

                foreach ($project->images as $image) {
                    if ($image->image) {
                        $files[] = $image->image;
                    }
                }

                $name = $project->name;

                // Gallery rows are removed automatically via cascadeOnDelete.
                $project->forceDelete();
            });

            /*
            |--------------------------------------------------------------------------
            | Delete Files
            |--------------------------------------------------------------------------
            */

            $this->deleteFiles($files);

            activity_log(
                'Project',
                'Permanently deleted project: '.($name ?? '')
            );

            return back()->with('success', 'Project permanently deleted.');

        } catch (\Throwable $e) {

            report($e);

            return back()->with('error', friendly_error($e));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Store Gallery Images
    |--------------------------------------------------------------------------
    */

    private function storeGalleryImages(Project $project, array $files): array
    {
        $files = array_filter($files);

        if (empty($files)) {
            return [];
        }

        $paths = [];

        $order = (int) $project->images()->max('display_order');

        foreach ($files as $file) {

            $path = $this->uploadImage($file, 'projects/gallery', $project->name);

            $paths[] = $path;

            $project->images()->create([
                'image' => $path,
                'display_order' => ++$order,
                'created_at' => now(),
            ]);
        }

        return $paths;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Sync Existing Gallery (caption / order / delete)
    |--------------------------------------------------------------------------
    */

    private function syncExistingGallery(Project $project, Request $request): array
    {
        $removedPaths = [];

        $deleteIds = array_map('intval', (array) $request->input('delete_images', []));

        $meta = (array) $request->input('images', []);

        foreach ($project->images()->get() as $image) {

            /*
            |--------------------------------------------------------------
            | Delete flagged images
            |--------------------------------------------------------------
            */

            if (in_array($image->id, $deleteIds, true)) {

                if ($image->image) {
                    $removedPaths[] = $image->image;
                }

                $image->delete();

                continue;
            }

            /*
            |--------------------------------------------------------------
            | Update caption / display order
            |--------------------------------------------------------------
            */

            if (isset($meta[$image->id])) {

                $image->update([
                    'caption' => $meta[$image->id]['caption'] ?? null,
                    'display_order' => (int) ($meta[$image->id]['display_order'] ?? $image->display_order),
                ]);
            }
        }

        return $removedPaths;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Upload Image
    |--------------------------------------------------------------------------
    */

    private function uploadImage(UploadedFile $image, string $folder, string $name): string
    {
        return app(ImageService::class)->store($image, $folder, $name);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Delete Files
    |--------------------------------------------------------------------------
    */

    private function deleteFiles(array $paths): void
    {
        $paths = array_filter($paths);

        if (! empty($paths)) {
            Storage::disk('public')->delete($paths);
        }
    }
}
