<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

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

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        $validated['slug'] = $this->generateUniqueSlug($validated['name']);

        try {

            $category = NewsCategory::create($validated);

            activity_log(
                'News Category',
                'Created news category: ' . $category->name
            );

            return redirect()
                ->route('admin.news-categories.index')
                ->with('success', 'News category created successfully.');

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with('error', 'Failed to create news category.');
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

    public function update(Request $request, NewsCategory $newsCategory)
    {
        $validated = $this->validateData($request);

        if ($newsCategory->name !== $validated['name']) {
            $validated['slug'] = $this->generateUniqueSlug(
                $validated['name'],
                $newsCategory->id
            );
        }

        try {

            $newsCategory->update($validated);

            activity_log(
                'News Category',
                'Updated news category: ' . $newsCategory->name
            );

            return redirect()
                ->route('admin.news-categories.index')
                ->with('success', 'News category updated successfully.');

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with('error', 'Failed to update news category.');
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

        if ($newsCategory->news()->exists()) {

            return back()->with(
                'error',
                'Cannot delete: this category is still used by one or more news articles.'
            );
        }

        try {

            DB::transaction(function () use ($newsCategory) {
                $newsCategory->delete();
            });

            activity_log(
                'News Category',
                'Deleted news category: ' . $newsCategory->name
            );

            return back()->with('success', 'News category deleted successfully.');

        } catch (\Throwable $e) {

            report($e);

            return back()->with('error', 'Failed to delete news category.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Validate
    |--------------------------------------------------------------------------
    */

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:191'],
        ]);
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
            $slug = $baseSlug . '-' . $count++;
        }

        return $slug;
    }
}
