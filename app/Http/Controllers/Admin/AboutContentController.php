<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutContent;
use App\Models\AboutSection;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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

                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
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

    public function store(Request $request)
    {
        // Key selalu diturunkan dari Title (field read-only di form).
        $request->merge([
            'key' => $this->generateKey($request->input('title')),
        ]);

        $validated = $request->validate([

            'section_id' => [
                'required',
                'exists:about_sections,id',
            ],

            'key' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('about_contents')
                    ->where(fn ($query) => $query->where('section_id', $request->section_id)),
            ],

            'title' => [
                'nullable',
                'string',
                'max:191',
            ],

            'content' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'display_order' => [

                'required',

                'integer',

                'min:1',

                Rule::unique('about_contents')
                    ->where(function ($query) use ($request) {

                        return $query->where(
                            'section_id',
                            $request->section_id
                        );
                    }),
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

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

                    $validated['title'] ?? 'about-content'
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
                    'Failed to create about content.'
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
        Request $request,
        AboutContent $aboutContent
    ) {

        // Key diturunkan dari Title; bila Title dikosongkan, pertahankan key lama.
        $request->merge([
            'key' => $this->generateKey($request->input('title'), $aboutContent->key),
        ]);

        $validated = $request->validate([

            'section_id' => [
                'required',
                'exists:about_sections,id',
            ],

            'key' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('about_contents')
                    ->where(fn ($query) => $query->where('section_id', $request->section_id))
                    ->ignore($aboutContent->id),
            ],

            'title' => [
                'nullable',
                'string',
                'max:191',
            ],

            'content' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'display_order' => [
                'required',
                'integer',
                'min:1',

                Rule::unique('about_contents')
                    ->where(function ($query) use ($request) {

                        return $query->where(
                            'section_id',
                            $request->section_id
                        );
                    })
                    ->ignore($aboutContent->id),
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

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

                    $validated['title'] ?? 'about-content'
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
                ->route('admin.about-contents.index')
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
                    'Failed to update about content.'
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

                'Failed to delete about content.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Key
    |--------------------------------------------------------------------------
    */

    /**
     * Bentuk identifier `key` dari Title (field read-only di form):
     * - utamakan title; bila kosong gunakan $fallback (mis. key lama saat edit);
     * - selalu di-slug (lowercase, underscore) agar konsisten dengan regex ^[a-z0-9_]+$;
     * - kembalikan null bila tidak ada sumber sama sekali.
     */
    private function generateKey(?string $title, ?string $fallback = null): ?string
    {
        $source = filled($title) ? $title : $fallback;

        if (blank($source)) {
            return null;
        }

        // Str::slug menghapus karakter spesial (mis. "Vision & Mission" -> "vision_mission").
        return Str::slug(trim($source), '_');
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
