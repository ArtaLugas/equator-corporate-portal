<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AuthorizesModuleActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HeroBannerRequest;
use App\Models\HeroBanner;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HeroBannerController extends Controller implements HasMiddleware
{
    use AuthorizesModuleActions;

    public static function middleware(): array
    {
        return static::moduleMiddleware('hero-banner');
    }

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

                foreach (array_keys(config('locales.supported', [])) as $locale) {
                    $q->orWhere("title_{$locale}", 'like', "%{$search}%")
                        ->orWhere("subtitle_{$locale}", 'like', "%{$search}%")
                        ->orWhere("button_text_{$locale}", 'like', "%{$search}%");
                }
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

                $query->orderBy('title_'.config('locales.default'));

                break;

            case 'title_desc':

                $query->orderByDesc('title_'.config('locales.default'));

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

    public function store(HeroBannerRequest $request)
    {
        $validated = $request->validated();

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

                    $validated['title_en'] ?? 'hero-banner'
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

                'Created hero banner: '.
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
                    friendly_error($e)
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
        HeroBannerRequest $request,
        HeroBanner $heroBanner
    ) {

        $validated = $request->validated();

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

                    $validated['title_en'] ?? 'hero-banner'
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

                'Updated hero banner: '.
                ($heroBanner->title ?? 'Untitled Banner')
            );

            return redirect()
                ->to(guarded_list_url($request->input('return_url'), route('admin.hero-banners.index')))
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
                    friendly_error($e)
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

                'Deleted hero banner: '.
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
