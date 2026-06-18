<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroBanner;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HeroBannerController extends Controller
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
        $query = HeroBanner::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%")
                    ->orWhere('button_text', 'like', "%{$search}%");
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

            case 'title_asc':

                $query->orderBy('title');

                break;

            case 'title_desc':

                $query->orderByDesc('title');

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

        $banners = $query
            ->paginate(self::PAGINATION)
            ->withQueryString();

        return view(
            'admin.hero-banners.index',
            compact('banners')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('admin.hero-banners.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([

            'title' => [
                'nullable',
                'string',
                'max:191',
            ],

            'subtitle' => [
                'nullable',
                'string',
                'max:255',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'button_text' => [
                'nullable',
                'string',
                'max:100',
            ],

            'button_link' => [
                'nullable',
                'url',
                'max:500',
            ],

            'display_order' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('hero_banners', 'display_order')->ignore($request->route('hero_banner')?->id),
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Default Display Order
        |--------------------------------------------------------------------------
        */

        $validated['display_order'] ??= 1;

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

                    'hero-banners',

                    $validated['title'] ?? 'hero-banner'
                );

                $validated['image'] = $imagePath;
            }

            /*
            |--------------------------------------------------------------------------
            | Create Banner
            |--------------------------------------------------------------------------
            */

            $banner = HeroBanner::create($validated);

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Hero Banner',

                'Created hero banner: ' .
                ($banner->title ?? 'Untitled Banner')
            );

            DB::commit();

            return redirect()
                ->route('admin.hero-banners.index')
                ->with(
                    'success',
                    'Hero banner created successfully.'
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
                    'Failed to create hero banner.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(HeroBanner $heroBanner)
    {
        return view(
            'admin.hero-banners.show',
            [
                'banner' => $heroBanner,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(HeroBanner $heroBanner)
    {
        return view(
            'admin.hero-banners.edit',
            [
                'banner' => $heroBanner,
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
        HeroBanner $heroBanner
    ) {

        $validated = $request->validate([

            'title' => [
                'nullable',
                'string',
                'max:191',
            ],

            'subtitle' => [
                'nullable',
                'string',
                'max:255',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'button_text' => [
                'nullable',
                'string',
                'max:100',
            ],

            'button_link' => [
                'nullable',
                'url',
                'max:500',
            ],

            'display_order' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('hero_banners', 'display_order')->ignore($request->route('hero_banner')?->id),
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Default Display Order
        |--------------------------------------------------------------------------
        */

        $validated['display_order'] ??= 1;

        $oldImage = $heroBanner->image;

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

                    'hero-banners',

                    $validated['title'] ?? 'hero-banner'
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
            | Update Banner
            |--------------------------------------------------------------------------
            */

            $heroBanner->update($validated);

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

                'Hero Banner',

                'Updated hero banner: ' .
                ($heroBanner->title ?? 'Untitled Banner')
            );

            return redirect()
                ->route('admin.hero-banners.index')
                ->with(
                    'success',
                    'Hero banner updated successfully.'
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
                    'Failed to update hero banner.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(HeroBanner $heroBanner)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | Delete Image
            |--------------------------------------------------------------------------
            */

            if ($heroBanner->image) {

                Storage::disk('public')
                    ->delete($heroBanner->image);
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Record
            |--------------------------------------------------------------------------
            */

            DB::transaction(function () use ($heroBanner) {

                $heroBanner->delete();
            });

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Hero Banner',

                'Deleted hero banner: ' .
                ($heroBanner->title ?? 'Untitled Banner')
            );

            return back()->with(

                'success',

                'Hero banner deleted successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(

                'error',

                'Failed to delete hero banner.'
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
