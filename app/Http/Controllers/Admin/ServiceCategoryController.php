<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceCategoryController extends Controller
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

    public function store(Request $request)
    {
        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:191',
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

            'display_order' => [
                'nullable',
                'integer',
                'min:1',
                'unique:service_categories,display_order',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Default Value
        |--------------------------------------------------------------------------
        */

        $validated['display_order'] ??= 1;

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

                    'service-categories',

                    $validated['name']
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

                'Created service category: ' . $category->name
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
                    'Failed to create service category.'
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
        Request $request,
        ServiceCategory $serviceCategory
    ) {

        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:191',
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

            'display_order' => [
                'nullable',
                'integer',
                'min:1',
                'unique:service_categories,display_order,' . $serviceCategory->id,
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Default Value
        |--------------------------------------------------------------------------
        */

        $validated['display_order'] ??= 1;

        /*
        |--------------------------------------------------------------------------
        | Preserve Slug
        |--------------------------------------------------------------------------
        */

        if ($serviceCategory->name !== $validated['name']) {

            $validated['slug'] = $this->generateUniqueSlug(

                $validated['name'],

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

                    $validated['name']
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

                'Updated service category: ' . $serviceCategory->name
            );

            return redirect()
                ->route('admin.service-categories.index')
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
                    'Failed to update service category.'
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

                    'Cannot delete category with related services.'
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

                'Moved category to trash: ' . $serviceCategory->name
            );

            return back()->with(

                'success',

                'Service category moved to trash.'
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(

                'error',

                'Failed to delete service category.'
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

                'Restored category: ' . $category->name
            );

            return back()->with(

                'success',

                'Service category restored successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(

                'error',

                'Failed to restore service category.'
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

                'Permanently deleted category: ' . $category->name
            );

            return back()->with(

                'success',

                'Service category permanently deleted.'
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(

                'error',

                'Failed to permanently delete service category.'
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

            ServiceCategory::withTrashed()

                ->where('slug', $slug)

                ->when(
                    $ignoreId,
                    fn ($query) =>
                    $query->where('id', '!=', $ignoreId)
                )

                ->exists()

        ) {

            $slug = $baseSlug . '-' . $count++;
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

        $filename =
            time() .
            '-' .
            Str::slug($name) .
            '.' .
            $image->getClientOriginalExtension();

        return $image->storeAs(
            $folder,
            $filename,
            'public'
        );
    }
}
