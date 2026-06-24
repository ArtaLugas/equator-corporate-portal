<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewsRequest;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsController extends Controller
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
        $query = News::query()
            ->with(['category', 'tags']);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                // Translatable title across every locale column, plus slug,
                // status, and the category name.
                foreach (array_keys(config('locales.supported', [])) as $locale) {
                    $q->orWhere("title_{$locale}", 'like', "%{$search}%");
                }

                $q->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', $request->featured);
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $request->tag));
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
            case 'most_viewed':
                $query->orderByDesc('views_count');
                break;
            case 'published':
                $query->orderByDesc('published_at');
                break;
            default:
                $query->latest();
                break;
        }

        $news = $query
            ->paginate(self::PAGINATION)
            ->withQueryString();

        $categories = NewsCategory::orderBy('name')->get();

        $tags = Tag::orderBy('name')->get();

        return view(
            'admin.news.index',
            compact('news', 'categories', 'tags')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $categories = NewsCategory::orderBy('name')->get();

        return view('admin.news.create', compact('categories'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(NewsRequest $request)
    {
        $validated = $request->validated();

        $validated['is_featured'] = $request->boolean('is_featured');

        $defaultTitle = $validated['title_'.config('locales.default')];

        $validated['slug'] = $this->generateUniqueSlug($defaultTitle);

        $validated['published_at'] = $this->resolvePublishedAt($validated);

        $imagePath = null;

        try {

            DB::beginTransaction();

            if ($request->hasFile('image')) {

                $imagePath = $this->uploadImage(
                    $request->file('image'),
                    'news',
                    $defaultTitle
                );

                $validated['image'] = $imagePath;
            }

            $news = News::create($validated);

            $this->syncTags($news, $request->input('tags', []));

            activity_log('News', 'Created news: '.$news->title);

            DB::commit();

            return redirect()
                ->route('admin.news.index')
                ->with('success', 'News created successfully.');

        } catch (\Throwable $e) {

            DB::rollBack();

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            report($e);

            return back()
                ->withInput()
                ->with('error', friendly_error($e));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(News $news)
    {
        $news->load(['category', 'tags']);

        return view('admin.news.show', compact('news'));
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(News $news)
    {
        $news->load('tags');

        $categories = NewsCategory::orderBy('name')->get();

        return view('admin.news.edit', compact('news', 'categories'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(NewsRequest $request, News $news)
    {
        $validated = $request->validated();

        $validated['is_featured'] = $request->boolean('is_featured');

        $defaultLocale = config('locales.default');

        $defaultTitle = $validated['title_'.$defaultLocale];

        // Regenerate the slug only when enabled (config) AND the default-locale
        // title actually changed.
        if (
            config('cms.auto_regenerate_slug', true)
            && $news->{'title_'.$defaultLocale} !== $defaultTitle
        ) {
            $validated['slug'] = $this->generateUniqueSlug($defaultTitle, $news->id);
        }

        $validated['published_at'] = $this->resolvePublishedAt($validated, $news);

        $oldImage = $news->image;

        $newImage = null;

        try {

            DB::beginTransaction();

            if ($request->hasFile('image')) {

                $newImage = $this->uploadImage(
                    $request->file('image'),
                    'news',
                    $defaultTitle
                );

                $validated['image'] = $newImage;
            }

            if (! $request->hasFile('image') && $request->boolean('remove_image')) {
                $validated['image'] = null;
            }

            $news->update($validated);

            $this->syncTags($news, $request->input('tags', []));

            DB::commit();

            if ($oldImage && ($newImage || $request->boolean('remove_image'))) {
                Storage::disk('public')->delete($oldImage);
            }

            activity_log('News', 'Updated news: '.$news->title);

            return redirect()
                ->to(guarded_list_url($request->input('return_url'), route('admin.news.index')))
                ->with('success', 'News updated successfully.');

        } catch (\Throwable $e) {

            DB::rollBack();

            if ($newImage) {
                Storage::disk('public')->delete($newImage);
            }

            report($e);

            return back()
                ->withInput()
                ->with('error', friendly_error($e));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY (soft delete)
    |--------------------------------------------------------------------------
    */

    public function destroy(News $news)
    {
        try {

            DB::transaction(fn () => $news->delete());

            activity_log('News', 'Moved news to trash: '.$news->title);

            return back()->with('success', 'News moved to trash.');

        } catch (\Throwable $e) {

            report($e);

            return back()->with('error', friendly_error($e));
        }
    }

    public function bulkDestroy()
    {
        $ids = array_map('intval', (array) request()->input('ids', []));

        if (empty($ids)) {
            return back()->with('error', __('flash.none_selected'));
        }

        $count = News::whereIn('id', $ids)->get()->each->delete()->count();

        activity_log('News', "Bulk moved {$count} article(s) to trash.");

        return back()->with('success', "{$count} article(s) moved to trash.");
    }

    /*
    |--------------------------------------------------------------------------
    | TRASH
    |--------------------------------------------------------------------------
    */

    public function trash()
    {
        $news = News::onlyTrashed()
            ->with('category')
            ->latest('deleted_at')
            ->paginate(self::PAGINATION);

        return view('admin.news.trash', compact('news'));
    }

    /*
    |--------------------------------------------------------------------------
    | RESTORE
    |--------------------------------------------------------------------------
    */

    public function restore(int $id)
    {
        try {

            $news = DB::transaction(function () use ($id) {
                $news = News::onlyTrashed()->findOrFail($id);
                $news->restore();

                return $news;
            });

            activity_log('News', 'Restored news: '.$news->title);

            return back()->with('success', 'News restored successfully.');

        } catch (\Throwable $e) {

            report($e);

            return back()->with('error', friendly_error($e));
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

        $title = null;

        try {

            DB::transaction(function () use ($id, &$imagePath, &$title) {

                $news = News::onlyTrashed()->findOrFail($id);

                $imagePath = $news->image;

                $title = $news->title;

                // news_tag rows removed automatically via cascadeOnDelete.
                $news->forceDelete();
            });

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            activity_log('News', 'Permanently deleted news: '.($title ?? ''));

            return back()->with('success', 'News permanently deleted.');

        } catch (\Throwable $e) {

            report($e);

            return back()->with('error', friendly_error($e));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Sync Tags (find or create by slug)
    |--------------------------------------------------------------------------
    */

    private function syncTags(News $news, array $tagNames): void
    {
        $ids = [];

        foreach ($tagNames as $name) {

            $name = trim((string) $name);

            if ($name === '') {
                continue;
            }

            $slug = Str::slug($name);

            if ($slug === '') {
                continue;
            }

            $tag = Tag::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'created_at' => now()]
            );

            $ids[] = $tag->id;
        }

        $news->tags()->sync($ids);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Resolve published_at
    |--------------------------------------------------------------------------
    */

    private function resolvePublishedAt(array $validated, ?News $news = null): ?string
    {
        // Explicit value provided.
        if (! empty($validated['published_at'])) {
            return $validated['published_at'];
        }

        // Auto-stamp when publishing without an explicit date.
        if (($validated['status'] ?? null) === 'published') {
            return $news?->published_at?->toDateTimeString() ?? now()->toDateTimeString();
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Unique Slug
    |--------------------------------------------------------------------------
    */

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);

        $slug = $baseSlug;

        $count = 1;

        while (
            News::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$count++;
        }

        return $slug;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Upload Image
    |--------------------------------------------------------------------------
    */

    private function uploadImage(UploadedFile $image, string $folder, string $name): string
    {
        $filename =
            time().'-'.Str::slug($name).'.'.$image->getClientOriginalExtension();

        return $image->storeAs($folder, $filename, 'public');
    }
}
