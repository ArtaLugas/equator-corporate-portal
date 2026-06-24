<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AboutContentRequest;
use App\Models\AboutContent;
use App\Models\AboutSection;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AboutContentController extends Controller
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
        $query = AboutContent::query()
            ->with('section');

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
                        ->orWhere("content_{$locale}", 'like', "%{$search}%");
                }

                $q->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('section', fn ($s) => $s->where('name_'.config('locales.default'), 'like', "%{$search}%"));
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Section Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('section')) {

            $query->where(
                'section_id',
                $request->section
            );
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

        $contents = $query
            ->paginate(self::PAGINATION)
            ->withQueryString();

        $sections = AboutSection::where(
            'status',
            'active'
        )->orderBy('name')->get();

        return view(
            'admin.about-contents.index',
            compact(
                'contents',
                'sections'
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
        $sections = AboutSection::where(
            'status',
            'active'
        )->orderBy('name')->get();

        return view(
            'admin.about-contents.create',
            compact('sections')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(AboutContentRequest $request)
    {
        $validated = $request->validated();

        $defaultTitle = $validated['title_'.config('locales.default')] ?? 'about-content';

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

                    'about-contents',

                    $defaultTitle
                );

                $validated['image'] = $imagePath;
            }

            /*
            |--------------------------------------------------------------------------
            | Create Content
            |--------------------------------------------------------------------------
            */

            $content = AboutContent::create($validated);

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'About Content',

                'Created about content: '.
                ($content->title ?? 'Untitled')
            );

            DB::commit();

            return redirect()
                ->route('admin.about-contents.index')
                ->with(
                    'success',
                    'About content created successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

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

    public function show(AboutContent $aboutContent)
    {
        $aboutContent->load('section');

        return view(
            'admin.about-contents.show',
            [
                'content' => $aboutContent,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(AboutContent $aboutContent)
    {
        $sections = AboutSection::where(
            'status',
            'active'
        )->orderBy('name')->get();

        return view(
            'admin.about-contents.edit',
            [
                'content' => $aboutContent,
                'sections' => $sections,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        AboutContentRequest $request,
        AboutContent $aboutContent
    ) {

        $validated = $request->validated();

        $defaultTitle = $validated['title_'.config('locales.default')] ?? 'about-content';

        $oldImage = $aboutContent->image;

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

                    'about-contents',

                    $defaultTitle
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
            | Update Content
            |--------------------------------------------------------------------------
            */

            $aboutContent->update($validated);

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

                'About Content',

                'Updated about content: '.
                ($aboutContent->title ?? 'Untitled')
            );

            return redirect()
                ->to(guarded_list_url($request->input('return_url'), route('admin.about-contents.index')))
                ->with(
                    'success',
                    'About content updated successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

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

    public function destroy(AboutContent $aboutContent)
    {
        try {

            if ($aboutContent->image) {

                Storage::disk('public')
                    ->delete($aboutContent->image);
            }

            DB::transaction(function () use ($aboutContent) {

                $aboutContent->delete();
            });

            activity_log(

                'About Content',

                'Deleted about content: '.
                ($aboutContent->title ?? 'Untitled')
            );

            return back()->with(

                'success',

                'About content deleted successfully.'
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
