<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AboutSectionRequest;
use App\Models\AboutSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AboutSectionController extends Controller
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
        $query = AboutSection::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                foreach (array_keys(config('locales.supported', [])) as $locale) {
                    $q->orWhere("name_{$locale}", 'like', "%{$search}%");
                }

                $q->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
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

        $sections = $query
            ->paginate(self::PAGINATION)
            ->withQueryString();

        return view(
            'admin.about-sections.index',
            compact('sections')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('admin.about-sections.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(AboutSectionRequest $request)
    {
        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Auto Slug Generator (from the default-locale name; internal identifier)
        |--------------------------------------------------------------------------
        */

        $baseSlug = Str::slug($validated['name_'.config('locales.default')]);

        $slug = $baseSlug;

        $count = 1;

        while (

            AboutSection::where('slug', $slug)
                ->exists()

        ) {

            $slug = $baseSlug.'-'.$count++;
        }

        $validated['slug'] = $slug;

        /*
        |--------------------------------------------------------------------------
        | Default Display Order
        |--------------------------------------------------------------------------
        */

        $validated['display_order'] ??= 1;

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Create Section
            |--------------------------------------------------------------------------
            */

            $section = AboutSection::create($validated);

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'About Section',

                'Created about section: '.
                $section->name
            );

            DB::commit();

            return redirect()
                ->route('admin.about-sections.index')
                ->with(
                    'success',
                    'About section created successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Failed to create about section.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(AboutSection $aboutSection)
    {
        return view(
            'admin.about-sections.show',
            [
                'section' => $aboutSection,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(AboutSection $aboutSection)
    {
        return view(
            'admin.about-sections.edit', compact('aboutSection')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        AboutSectionRequest $request,
        AboutSection $aboutSection
    ) {

        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Auto Slug Generator — regenerate from the default-locale name only when
        | enabled (config) AND the name actually changed. Slug is internal.
        |--------------------------------------------------------------------------
        */

        $defaultLocale = config('locales.default');

        $defaultName = $validated['name_'.$defaultLocale];

        if (
            config('cms.auto_regenerate_slug', true)
            && $aboutSection->{'name_'.$defaultLocale} !== $defaultName
        ) {

            $baseSlug = Str::slug($defaultName);

            $slug = $baseSlug;

            $count = 1;

            while (

                AboutSection::where('slug', $slug)
                    ->where('id', '!=', $aboutSection->id)
                    ->exists()

            ) {

                $slug = $baseSlug.'-'.$count++;
            }

            $validated['slug'] = $slug;
        }

        /*
        |--------------------------------------------------------------------------
        | Default Display Order
        |--------------------------------------------------------------------------
        */

        $validated['display_order'] ??= 1;

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Update Section
            |--------------------------------------------------------------------------
            */

            $aboutSection->update($validated);

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'About Section',

                'Updated about section: '.
                $aboutSection->name
            );

            DB::commit();

            return redirect()
                ->route('admin.about-sections.index')
                ->with(
                    'success',
                    'About section updated successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Failed to update about section.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(AboutSection $aboutSection)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | Prevent Delete If Has Related Contents
            |--------------------------------------------------------------------------
            */

            if (

                method_exists($aboutSection, 'contents')
                && $aboutSection->contents()->exists()

            ) {

                return back()->with(

                    'error',

                    'Cannot delete section with related contents.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Section
            |--------------------------------------------------------------------------
            */

            DB::transaction(function () use ($aboutSection) {

                $aboutSection->delete();
            });

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'About Section',

                'Deleted about section: '.
                $aboutSection->name
            );

            return redirect()
                ->route('admin.about-sections.index')
                ->with(
                    'success',
                    'About section deleted successfully.'
                );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(

                'error',

                'Failed to delete about section.'
            );
        }
    }
}
