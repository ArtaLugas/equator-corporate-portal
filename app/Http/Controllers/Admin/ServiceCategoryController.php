<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\GeneratesUniqueSlug;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceCategoryRequest;
use App\Models\ServiceCategory;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServiceCategoryController extends Controller
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
        $query = ServiceCategory::query()
            ->withCount('services');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                // Name is translatable — search every locale column, plus slug.
                foreach (array_keys(config('locales.supported', [])) as $locale) {
                    $q->orWhere("name_{$locale}", 'like', "%{$search}%");
                }

                $q->orWhere('slug', 'like', "%{$search}%");
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

            case 'display_order':

                $query->orderBy('display_order');

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

        $categories = $query
            ->paginate(self::PAGINATION)
            ->withQueryString();

        return view(
            'admin.service-categories.index',
            compact('categories')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('admin.service-categories.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(ServiceCategoryRequest $request)
    {
        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Default Value
        |--------------------------------------------------------------------------
        */

        $validated['display_order'] ??= 1;

        /*
        |--------------------------------------------------------------------------
        | Generate Slug (from the default-locale name)
        |--------------------------------------------------------------------------
        */

        $defaultName = $validated['name_'.config('locales.default')];

        $validated['slug'] = $this->generateUniqueSlug(
            ServiceCategory::class,
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

                    'service-categories',

                    $defaultName
                );

                $validated['image'] = $imagePath;
            }

            /*
            |--------------------------------------------------------------------------
            | Create Category
            |--------------------------------------------------------------------------
            */

            $category = ServiceCategory::create($validated);

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Service Category',

                'Created service category: '.$category->name
            );

            DB::commit();

            return redirect()
                ->route('admin.service-categories.index')
                ->with(
                    'success',
                    'Service category created successfully.'
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

    public function show(ServiceCategory $serviceCategory)
    {
        $serviceCategory->loadCount('services');

        return view(
            'admin.service-categories.show',
            [
                'category' => $serviceCategory,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(ServiceCategory $serviceCategory)
    {
        return view(
            'admin.service-categories.edit',
            [
                'category' => $serviceCategory,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        ServiceCategoryRequest $request,
        ServiceCategory $serviceCategory
    ) {

        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Default Value
        |--------------------------------------------------------------------------
        */

        $validated['display_order'] ??= 1;

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
            && $serviceCategory->{'name_'.$defaultLocale} !== $defaultName
        ) {

            $validated['slug'] = $this->generateUniqueSlug(

                ServiceCategory::class,

                $defaultName,

                $serviceCategory->id
            );
        }

        $oldImage = $serviceCategory->image;

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

                    'service-categories',

                    $defaultName
                );

                $validated['image'] = $newImage;
            }

            /*
            |--------------------------------------------------------------------------
            | Remove Image
            |--------------------------------------------------------------------------
            */

            if (! $request->hasFile('image') && $request->boolean('remove_image')) {

                $validated['image'] = null;
            }

            /*
            |--------------------------------------------------------------------------
            | Update Category
            |--------------------------------------------------------------------------
            */

            $serviceCategory->update($validated);

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

                'Service Category',

                'Updated service category: '.$serviceCategory->name
            );

            return redirect()
                ->to(guarded_list_url($request->input('return_url'), route('admin.service-categories.index')))
                ->with(
                    'success',
                    'Service category updated successfully.'
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

    public function destroy(ServiceCategory $serviceCategory)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | Prevent Delete If Has Related Services
            |--------------------------------------------------------------------------
            */

            if (
                $serviceCategory
                    ->services()
                    ->withTrashed()
                    ->exists()
            ) {

                return back()->with(

                    'error',

                    __('flash.in_use')
                );
            }

            DB::transaction(function () use ($serviceCategory) {

                $serviceCategory->delete();
            });

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Service Category',

                'Moved category to trash: '.$serviceCategory->name
            );

            return back()->with(

                'success',

                'Service category moved to trash.'
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(

                'error',

                friendly_error($e)
            );
        }
    }

    public function bulkDestroy()
    {
        $ids = array_map('intval', (array) request()->input('ids', []));

        if (empty($ids)) {
            return back()->with('error', __('flash.none_selected'));
        }

        $count = ServiceCategory::whereIn('id', $ids)->get()->each->delete()->count();

        activity_log('Service Category', "Bulk moved {$count} category(s) to trash.");

        return back()->with('success', "{$count} category(s) moved to trash.");
    }

    /*
    |--------------------------------------------------------------------------
    | TRASH
    |--------------------------------------------------------------------------
    */

    public function trash()
    {
        $categories = ServiceCategory::onlyTrashed()
            ->withCount('services')
            ->latest()
            ->paginate(self::PAGINATION);

        return view(
            'admin.service-categories.trash',
            compact('categories')
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

            $category = DB::transaction(function () use ($id) {

                $category = ServiceCategory::onlyTrashed()
                    ->findOrFail($id);

                $category->restore();

                return $category;
            });

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Service Category',

                'Restored category: '.$category->name
            );

            return back()->with(

                'success',

                'Service category restored successfully.'
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

            $category = DB::transaction(function () use (

                $id,

                &$imagePath

            ) {

                $category = ServiceCategory::onlyTrashed()
                    ->findOrFail($id);

                /*
                |--------------------------------------------------------------------------
                | Prevent Delete If Has Related Services
                |--------------------------------------------------------------------------
                */

                if (
                    $category
                        ->services()
                        ->withTrashed()
                        ->exists()
                ) {

                    throw new \Exception(
                        'Category still has related services.'
                    );
                }

                $imagePath = $category->image;

                $category->forceDelete();

                return $category;
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

                'Service Category',

                'Permanently deleted category: '.$category->name
            );

            return back()->with(

                'success',

                'Service category permanently deleted.'
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
