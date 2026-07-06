<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewsCategoryRequest;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsCategoryController extends Controller
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
        $query = NewsCategory::query()
            ->withCount('news');

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

        $defaultName = 'name_'.config('locales.default');

        switch ($request->sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'name_asc':
                $query->orderBy($defaultName);
                break;
            case 'name_desc':
                $query->orderByDesc($defaultName);
                break;
            default:
                $query->latest();
                break;
        }

        $categories = $query
            ->paginate(self::PAGINATION)
            ->withQueryString();

        return view(
            'admin.news-categories.index',
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
        return view('admin.news-categories.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(NewsCategoryRequest $request)
    {
        $validated = $request->validated();

        // Slug derives from the default-locale name and stays stable across locales.
        $defaultName = $validated['name_'.config('locales.default')];

        $validated['slug'] = $this->generateUniqueSlug($defaultName);

        try {

            $category = NewsCategory::create($validated);

            activity_log(
                'News Category',
                'Created news category: '.$category->name
            );

            return redirect()
                ->route('admin.news-categories.index')
                ->with('success', 'News category created successfully.');

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with('error', friendly_error($e));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(NewsCategory $newsCategory)
    {
        return view(
            'admin.news-categories.edit',
            compact('newsCategory')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(NewsCategoryRequest $request, NewsCategory $newsCategory)
    {
        $validated = $request->validated();

        $default = config('locales.default');
        $defaultName = $validated['name_'.$default];

        // Regenerate the slug only when the default-locale name actually changed.
        if ($newsCategory->{'name_'.$default} !== $defaultName) {
            $validated['slug'] = $this->generateUniqueSlug(
                $defaultName,
                $newsCategory->id
            );
        }

        try {

            $newsCategory->update($validated);

            activity_log(
                'News Category',
                'Updated news category: '.$newsCategory->name
            );

            return redirect()
                ->to(guarded_list_url($request->input('return_url'), route('admin.news-categories.index')))
                ->with('success', 'News category updated successfully.');

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with('error', friendly_error($e));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(NewsCategory $newsCategory)
    {
        /*
        |--------------------------------------------------------------------------
        | Prevent deletion when still referenced by news (restrictOnDelete)
        |--------------------------------------------------------------------------
        */

        // Count trashed articles too: News soft-deletes, but the DB foreign key
        // (restrictOnDelete) still references the category from trashed rows — so
        // without withTrashed() the guard would pass and the delete would then fail
        // with a raw FK violation. Mirrors ServiceCategoryController::destroy().
        if ($newsCategory->news()->withTrashed()->exists()) {

            return back()->with(
                'error',
                __('flash.in_use')
            );
        }

        try {

            DB::transaction(function () use ($newsCategory) {
                $newsCategory->delete();
            });

            activity_log(
                'News Category',
                'Deleted news category: '.$newsCategory->name
            );

            return back()->with('success', 'News category deleted successfully.');

        } catch (\Throwable $e) {

            report($e);

            return back()->with('error', friendly_error($e));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Unique Slug
    |--------------------------------------------------------------------------
    */

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);

        $slug = $baseSlug;

        $count = 1;

        while (
            NewsCategory::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$count++;
        }

        return $slug;
    }
}
