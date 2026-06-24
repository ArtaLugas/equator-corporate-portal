<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\GeneratesUniqueSlug;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
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
        $query = Service::query()

            ->with([
                'category' => fn ($q) => $q->withTrashed(),
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                // Translatable name across every locale column, plus slug, status,
                // and the (non-translatable) category name — a complete admin search.
                foreach (array_keys(config('locales.supported', [])) as $locale) {
                    $q->orWhere("name_{$locale}", 'like', "%{$search}%");
                }

                $q->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($c) use ($search) {
                        // Category name is translatable — search every locale column.
                        foreach (array_keys(config('locales.supported', [])) as $locale) {
                            $c->orWhere("name_{$locale}", 'like', "%{$search}%");
                        }
                    });
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
        | Category Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category')) {

            $query->where(
                'category_id',
                $request->category
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

                $query->oldest();

                break;

            case 'name_asc':

                $query->orderBy('name_'.config('locales.default'));

                break;

            case 'name_desc':

                $query->orderByDesc('name_'.config('locales.default'));

                break;

            default:

                $query->latest();

                break;
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $services = $query
            ->paginate(self::PAGINATION)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        $categories = ServiceCategory::query()
            ->where('status', 'active')
            ->orderBy('name_'.config('locales.default'))
            ->get();

        return view(
            'admin.services.index',
            compact(
                'services',
                'categories'
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
        $categories = ServiceCategory::query()

            ->where('status', 'active')

            ->orderBy('name_'.config('locales.default'))

            ->get();

        return view(
            'admin.services.create',
            compact('categories')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(ServiceRequest $request)
    {
        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Boolean Normalize
        |--------------------------------------------------------------------------
        */

        $validated['is_featured'] =
            $request->boolean('is_featured');

        /*
        |--------------------------------------------------------------------------
        | Generate Slug (from the default-locale name)
        |--------------------------------------------------------------------------
        */

        $defaultName = $validated['name_'.config('locales.default')];

        $validated['slug'] = $this->generateUniqueSlug(
            Service::class,
            $defaultName
        );

        $imagePath = null;

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Upload Image
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('image')) {

                $imagePath = $this->uploadImage(

                    $request->file('image'),

                    'services',

                    $defaultName
                );

                $validated['image'] = $imagePath;
            }

            /*
            |--------------------------------------------------------------------------
            | Create Service
            |--------------------------------------------------------------------------
            */

            $service = Service::create($validated);

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Service',

                'Created service: '.$service->name
            );

            DB::commit();

            return redirect()
                ->route('admin.services.index')
                ->with(
                    'success',
                    'Service created successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            /*
            |--------------------------------------------------------------------------
            | Cleanup Uploaded Image
            |--------------------------------------------------------------------------
            */

            if ($imagePath) {

                Storage::disk('public')
                    ->delete($imagePath);
            }

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    friendly_error($e)
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(Service $service)
    {
        $service->load([
            'category' => fn ($q) => $q->withTrashed(),
        ]);

        return view(
            'admin.services.show',
            compact('service')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(Service $service)
    {
        $categories = ServiceCategory::query()

            ->where('status', 'active')

            ->orderBy('name_'.config('locales.default'))

            ->get();

        return view(
            'admin.services.edit',
            compact(
                'service',
                'categories'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        ServiceRequest $request,
        Service $service
    ) {

        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Boolean Normalize
        |--------------------------------------------------------------------------
        */

        $validated['is_featured'] =
            $request->boolean('is_featured');

        /*
        |--------------------------------------------------------------------------
        | Preserve Slug (regenerate only when the default-locale name changes)
        |--------------------------------------------------------------------------
        */

        $defaultLocale = config('locales.default');

        $defaultName = $validated['name_'.$defaultLocale];

        // Regenerate the slug only when enabled (config) AND the default-locale
        // name actually changed — keeps it efficient and lets permalinks be
        // frozen after go-live by flipping cms.auto_regenerate_slug to false.
        if (
            config('cms.auto_regenerate_slug', true)
            && $service->{'name_'.$defaultLocale} !== $defaultName
        ) {

            $validated['slug'] = $this->generateUniqueSlug(

                Service::class,

                $defaultName,

                $service->id
            );
        }

        $oldImage = $service->image;

        $newImage = null;

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Upload New Image
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('image')) {

                $newImage = $this->uploadImage(

                    $request->file('image'),

                    'services',

                    $defaultName
                );

                $validated['image'] = $newImage;
            }

            /*
            |--------------------------------------------------------------------------
            | Remove Existing Image
            |--------------------------------------------------------------------------
            */

            if (! $request->hasFile('image') && $request->boolean('remove_image')) {

                $validated['image'] = null;
            }

            /*
            |--------------------------------------------------------------------------
            | Update Service
            |--------------------------------------------------------------------------
            */

            $service->update($validated);

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Delete Old Image
            |--------------------------------------------------------------------------
            */

            if ($newImage && $oldImage) {

                Storage::disk('public')
                    ->delete($oldImage);
            }

            /*
            |--------------------------------------------------------------------------
            | Remove Existing Image
            |--------------------------------------------------------------------------
            */

            if (
                $request->boolean('remove_image')
                && $oldImage
            ) {

                Storage::disk('public')
                    ->delete($oldImage);
            }

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Service',

                'Updated service: '.$service->name
            );

            return redirect()
                ->to(guarded_list_url($request->input('return_url'), route('admin.services.index')))
                ->with(
                    'success',
                    'Service updated successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            /*
            |--------------------------------------------------------------------------
            | Cleanup Uploaded Image
            |--------------------------------------------------------------------------
            */

            if ($newImage) {

                Storage::disk('public')
                    ->delete($newImage);
            }

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    friendly_error($e)
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(Service $service)
    {
        try {

            DB::transaction(function () use ($service) {

                $service->delete();
            });

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Service',

                'Moved service to trash: '.$service->name
            );

            return back()->with(

                'success',

                'Service moved to trash.'
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(

                'error',

                friendly_error($e)
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | BULK DESTROY
    |--------------------------------------------------------------------------
    */

    public function bulkDestroy()
    {
        $ids = array_map('intval', (array) request()->input('ids', []));

        if (empty($ids)) {
            return back()->with('error', __('flash.none_selected'));
        }

        $count = Service::whereIn('id', $ids)->get()->each->delete()->count();

        activity_log('Service', "Bulk moved {$count} service(s) to trash.");

        return back()->with('success', "{$count} service(s) moved to trash.");
    }

    /*
    |--------------------------------------------------------------------------
    | TRASH
    |--------------------------------------------------------------------------
    */

    public function trash()
    {
        $services = Service::onlyTrashed()

            ->with([
                'category' => fn ($q) => $q->withTrashed(),
            ])

            ->latest()

            ->paginate(self::PAGINATION);

        return view(
            'admin.services.trash',
            compact('services')
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

            $service = DB::transaction(function () use ($id) {

                $service = Service::onlyTrashed()
                    ->findOrFail($id);

                $service->restore();

                return $service;
            });

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Service',

                'Restored service: '.$service->name
            );

            return back()->with(

                'success',

                'Service restored successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(

                'error',

                friendly_error($e)
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FORCE DELETE
    |--------------------------------------------------------------------------
    */

    public function forceDelete(int $id)
    {
        $imagePath = null;

        try {

            $service = DB::transaction(function () use (

                $id,

                &$imagePath

            ) {

                $service = Service::onlyTrashed()
                    ->findOrFail($id);

                $imagePath = $service->image;

                $service->forceDelete();

                return $service;
            });

            /*
            |--------------------------------------------------------------------------
            | Delete Image
            |--------------------------------------------------------------------------
            */

            if ($imagePath) {

                Storage::disk('public')
                    ->delete($imagePath);
            }

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Service',

                'Permanently deleted service: '.$service->name
            );

            return back()->with(

                'success',

                'Service permanently deleted.'
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(

                'error',

                friendly_error($e)
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Upload Image
    |--------------------------------------------------------------------------
    */

    private function uploadImage(
        UploadedFile $image,
        string $folder,
        string $name
    ): string {

        return app(ImageService::class)->store($image, $folder, $name);
    }
}
