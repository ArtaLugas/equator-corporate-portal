<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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

    public function store(Request $request)
    {
        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:191',
            ],

            'display_order' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('about_sections', 'display_order'),
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ], [
            'display_order.unique' => 'This display order is already used by another section.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Auto Slug Generator
        |--------------------------------------------------------------------------
        */

        $baseSlug = Str::slug($validated['name']);

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
        Request $request,
        AboutSection $aboutSection
    ) {

        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:191',
            ],

            'display_order' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('about_sections', 'display_order')->ignore($aboutSection->id),
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ], [
            'display_order.unique' => 'This display order is already used by another section.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Auto Slug Generator
        |--------------------------------------------------------------------------
        */

        $baseSlug = Str::slug($validated['name']);

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
