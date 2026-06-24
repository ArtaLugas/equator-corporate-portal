<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\GeneratesUniqueSlug;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyCredentialRequest;
use App\Models\CompanyCredential;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyCredentialController extends Controller
{
    use GeneratesUniqueSlug;

    private const PAGINATION = 10;

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CompanyCredential::class);

        $query = CompanyCredential::query()
            ->search($request->search)
            ->category($request->category)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('featured'), fn ($q) => $q->where('featured', $request->boolean('featured')));

        $default = config('locales.default');

        match ($request->sort) {
            'oldest' => $query->oldest(),
            'title_asc' => $query->orderBy("title_{$default}"),
            'title_desc' => $query->orderByDesc("title_{$default}"),
            default => $query->ordered(),
        };

        $credentials = $query->withCount('items')->paginate(self::PAGINATION)->withQueryString();

        $categories = array_keys(config('credentials.categories', []));

        return view('admin.company-credentials.index', compact('credentials', 'categories'));
    }

    public function create()
    {
        $this->authorize('create', CompanyCredential::class);

        $categories = array_keys(config('credentials.categories', []));

        return view('admin.company-credentials.create', compact('categories'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(CompanyCredentialRequest $request)
    {
        $this->authorize('create', CompanyCredential::class);

        $validated = $request->validated();
        $default = config('locales.default');
        $defaultTitle = $validated["title_{$default}"];

        $validated['featured'] = $request->boolean('featured');
        $validated['slug'] = $this->generateUniqueSlug(CompanyCredential::class, $defaultTitle);

        $imagePath = $attachmentPath = null;

        try {
            DB::beginTransaction();

            if ($request->hasFile('image')) {
                $validated['image'] = $imagePath = $this->uploadImage($request->file('image'), 'credentials', $defaultTitle);
            }

            if ($request->hasFile('attachment')) {
                $validated['attachment'] = $attachmentPath = $this->storeAttachment($request->file('attachment'));
            }

            $credential = CompanyCredential::create($validated);

            $this->syncItems($credential, (array) $request->input('items', []));

            activity_log('Company Credential', 'Created credential: '.$credential->title);

            DB::commit();

            return redirect()->route('admin.company-credentials.index')
                ->with('success', 'Credential created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->deleteFiles([$imagePath, $attachmentPath]);
            report($e);

            return back()->withInput()->with('error', friendly_error($e));
        }
    }

    public function show(CompanyCredential $companyCredential)
    {
        $this->authorize('view', $companyCredential);

        $companyCredential->load('items');

        return view('admin.company-credentials.show', ['credential' => $companyCredential]);
    }

    public function edit(CompanyCredential $companyCredential)
    {
        $this->authorize('update', $companyCredential);

        $companyCredential->load('items');
        $categories = array_keys(config('credentials.categories', []));

        return view('admin.company-credentials.edit', ['credential' => $companyCredential, 'categories' => $categories]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(CompanyCredentialRequest $request, CompanyCredential $companyCredential)
    {
        $this->authorize('update', $companyCredential);

        $validated = $request->validated();
        $default = config('locales.default');
        $defaultTitle = $validated["title_{$default}"];

        $validated['featured'] = $request->boolean('featured');

        // Regenerate slug only when enabled AND the default-locale title changed.
        if (config('cms.auto_regenerate_slug', true) && $companyCredential->{"title_{$default}"} !== $defaultTitle) {
            $validated['slug'] = $this->generateUniqueSlug(CompanyCredential::class, $defaultTitle, $companyCredential->id);
        }

        $oldImage = $companyCredential->image;
        $oldAttachment = $companyCredential->attachment;
        $newImage = $newAttachment = null;

        try {
            DB::beginTransaction();

            if ($request->hasFile('image')) {
                $validated['image'] = $newImage = $this->uploadImage($request->file('image'), 'credentials', $defaultTitle);
            } elseif ($request->boolean('remove_image')) {
                $validated['image'] = null;
            }

            if ($request->hasFile('attachment')) {
                $validated['attachment'] = $newAttachment = $this->storeAttachment($request->file('attachment'));
            } elseif ($request->boolean('remove_attachment')) {
                $validated['attachment'] = null;
            }

            $companyCredential->update($validated);

            $this->syncItems(
                $companyCredential,
                (array) $request->input('items', []),
                array_map('intval', (array) $request->input('deleted_items', []))
            );

            activity_log('Company Credential', 'Updated credential: '.$companyCredential->title);

            DB::commit();

            // Clean up replaced/removed files after the row is safely committed.
            if ($newImage && $oldImage) {
                $this->deleteFiles([$oldImage]);
            }
            if ($request->boolean('remove_image') && ! $newImage && $oldImage) {
                $this->deleteFiles([$oldImage]);
            }
            if ($newAttachment && $oldAttachment) {
                $this->deleteFiles([$oldAttachment]);
            }
            if ($request->boolean('remove_attachment') && ! $newAttachment && $oldAttachment) {
                $this->deleteFiles([$oldAttachment]);
            }

            return redirect()->to(guarded_list_url($request->input('return_url'), route('admin.company-credentials.index')))
                ->with('success', 'Credential updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->deleteFiles([$newImage, $newAttachment]);
            report($e);

            return back()->withInput()->with('error', friendly_error($e));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY / TRASH / RESTORE / FORCE DELETE / BULK
    |--------------------------------------------------------------------------
    */
    public function destroy(CompanyCredential $companyCredential)
    {
        $this->authorize('delete', $companyCredential);

        try {
            DB::transaction(fn () => $companyCredential->delete());
            activity_log('Company Credential', 'Moved credential to trash: '.$companyCredential->title);

            return back()->with('success', 'Credential moved to trash.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', friendly_error($e));
        }
    }

    public function trash()
    {
        $this->authorize('viewTrash', CompanyCredential::class);

        $credentials = CompanyCredential::onlyTrashed()->latest('deleted_at')->paginate(self::PAGINATION);

        return view('admin.company-credentials.trash', compact('credentials'));
    }

    public function restore(int $id)
    {
        $this->authorize('restore', CompanyCredential::class);

        try {
            $credential = DB::transaction(function () use ($id) {
                $c = CompanyCredential::onlyTrashed()->findOrFail($id);
                $c->restore();

                return $c;
            });
            activity_log('Company Credential', 'Restored credential: '.$credential->title);

            return back()->with('success', 'Credential restored successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', friendly_error($e));
        }
    }

    public function forceDelete(int $id)
    {
        $this->authorize('forceDelete', CompanyCredential::class);

        $files = [];

        try {
            $credential = DB::transaction(function () use ($id, &$files) {
                $c = CompanyCredential::onlyTrashed()->findOrFail($id);
                $files = [$c->image, $c->attachment];
                $c->forceDelete(); // items cascade via FK

                return $c;
            });

            $this->deleteFiles($files);
            activity_log('Company Credential', 'Permanently deleted credential: '.$credential->title);

            return back()->with('success', 'Credential permanently deleted.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', friendly_error($e));
        }
    }

    public function bulkDestroy(Request $request)
    {
        $this->authorize('delete', CompanyCredential::class);

        $ids = array_map('intval', (array) $request->input('ids', []));

        if (empty($ids)) {
            return back()->with('error', __('flash.none_selected'));
        }

        $count = CompanyCredential::whereIn('id', $ids)->get()->each->delete()->count();

        activity_log('Company Credential', "Bulk moved {$count} credential(s) to trash.");

        return back()->with('success', "{$count} credential(s) moved to trash.");
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    private function syncItems(CompanyCredential $credential, array $items, array $deletedIds = []): void
    {
        if ($deletedIds) {
            $credential->items()->whereIn('id', $deletedIds)->delete();
        }

        foreach (array_values($items) as $i => $row) {
            if (blank($row['title_en'] ?? null)) {
                continue; // skip empty repeater rows
            }

            $data = [
                'title_en' => $row['title_en'],
                'title_id' => $row['title_id'] ?? null,
                'description_en' => $row['description_en'] ?? null,
                'description_id' => $row['description_id'] ?? null,
                'display_order' => (int) ($row['display_order'] ?? $i),
            ];

            if (! empty($row['id'])) {
                $credential->items()->whereKey((int) $row['id'])->update($data);
            } else {
                $credential->items()->create($data);
            }
        }
    }

    private function uploadImage(UploadedFile $image, string $folder, string $name): string
    {
        return app(ImageService::class)->store($image, $folder, $name);
    }

    private function storeAttachment(UploadedFile $file): string
    {
        $filename = time().'-'.Str::random(6).'.'.$file->getClientOriginalExtension();

        return $file->storeAs('credentials/attachments', $filename, 'public');
    }

    private function deleteFiles(array $paths): void
    {
        $paths = array_filter($paths);

        if (! empty($paths)) {
            Storage::disk('public')->delete($paths);
        }
    }
}
