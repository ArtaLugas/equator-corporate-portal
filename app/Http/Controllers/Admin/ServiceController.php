<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
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

                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
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

                $query->orderBy('name');

                break;

            case 'name_desc':

                $query->orderByDesc('name');

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
            ->orderBy('name')
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

            ->orderBy('name')

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

    public function store(Request $request)
    {
        $validated = $request->validate([

            'category_id' => [
                'required',
                'exists:service_categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:191',
            ],

            'short_description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'meta_title' => [
                'nullable',
                'string',
                'max:191',
            ],

            'meta_description' => [
                'nullable',
                'string',
                'max:320',
            ],

            'meta_keywords' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                'in:draft,published',
            ],

            'is_featured' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Boolean Normalize
        |--------------------------------------------------------------------------
        */

        $validated['is_featured'] =
            $request->boolean('is_featured');

        /*
        |--------------------------------------------------------------------------
        | Generate Slug
        |--------------------------------------------------------------------------
        */

        $validated['slug'] = $this->generateUniqueSlug(
            $validated['name']
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

                    $validated['name']
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
                    'Failed to create service.'
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

            ->orderBy('name')

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
        Request $request,
        Service $service
    ) {

        $validated = $request->validate([

            'category_id' => [
                'required',
                'exists:service_categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:191',
            ],

            'short_description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'meta_title' => [
                'nullable',
                'string',
                'max:191',
            ],

            'meta_description' => [
                'nullable',
                'string',
                'max:320',
            ],

            'meta_keywords' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                'in:draft,published',
            ],

            'is_featured' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Boolean Normalize
        |--------------------------------------------------------------------------
        */

        $validated['is_featured'] =
            $request->boolean('is_featured');

        /*
        |--------------------------------------------------------------------------
        | Preserve Slug
        |--------------------------------------------------------------------------
        */

        if ($service->name !== $validated['name']) {

            $validated['slug'] = $this->generateUniqueSlug(

                $validated['name'],

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

                    $validated['name']
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
                ->route('admin.services.index')
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
                    'Failed to update service.'
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

                'Failed to delete service.'
            );
        }
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

                'Failed to restore service.'
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

                'Failed to permanently delete service.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Unique Slug
    |--------------------------------------------------------------------------
    */

    private function generateUniqueSlug(
        string $name,
        ?int $ignoreId = null
    ): string {

        $baseSlug = Str::slug($name);

        $slug = $baseSlug;

        $count = 1;

        while (

            Service::withTrashed()

                ->where('slug', $slug)

                ->when(
                    $ignoreId,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )

                ->exists()

        ) {

            $slug = $baseSlug.'-'.$count++;
        }

        return $slug;
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
