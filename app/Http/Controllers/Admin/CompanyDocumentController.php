<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\GeneratesUniqueSlug;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyDocumentRequest;
use App\Models\CompanyDocument;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyDocumentController extends Controller
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
        $query = CompanyDocument::query();

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                // Translatable title across every locale column, plus the
                // (non-translatable) document_type.
                foreach (array_keys(config('locales.supported', [])) as $locale) {
                    $q->orWhere("title_{$locale}", 'like', "%{$search}%");
                }

                $q->orWhere('document_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }

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

            case 'downloads':
                $query->orderByDesc('download_count');
                break;

            default:
                $query->latest();
                break;
        }

        $documents = $query
            ->paginate(self::PAGINATION)
            ->withQueryString();

        return view(
            'admin.company-documents.index',
            compact('documents')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view(
            'admin.company-documents.create'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(CompanyDocumentRequest $request)
    {
        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Auto Slug (from the default-locale title)
        |--------------------------------------------------------------------------
        */

        $defaultTitle = $validated['title_'.config('locales.default')];

        $validated['slug'] = $this->generateUniqueSlug(
            CompanyDocument::class,
            $defaultTitle
        );

        $filePath = null;
        $thumbnailPath = null;

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Single Active Company Profile
            |--------------------------------------------------------------------------
            */

            if (
                ($validated['document_type'] ?? null) === 'company_profile'
                &&
                $validated['status'] === 'active'
            ) {

                CompanyDocument::where(
                    'document_type',
                    'company_profile'
                )
                    ->update([
                        'status' => 'inactive',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Upload PDF
            |--------------------------------------------------------------------------
            */

            $filePath = $this->uploadFile(

                $request->file('file'),

                'company-documents',

                $defaultTitle
            );

            $validated['file'] = $filePath;

            $validated['file_size'] =
                $request->file('file')->getSize();

            /*
            |--------------------------------------------------------------------------
            | Upload Thumbnail
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('thumbnail')) {

                $thumbnailPath = $this->uploadImage(

                    $request->file('thumbnail'),

                    'company-documents/thumbnails',

                    $defaultTitle
                );

                $validated['thumbnail'] =
                    $thumbnailPath;
            }

            $document =
                CompanyDocument::create(
                    $validated
                );

            activity_log(

                'Company Document',

                'Created document: '
                    .$document->title
            );

            DB::commit();

            return redirect()
                ->route(
                    'admin.company-documents.index'
                )
                ->with(
                    'success',
                    'Document created successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            if ($filePath) {

                Storage::disk('public')
                    ->delete($filePath);
            }

            if ($thumbnailPath) {

                Storage::disk('public')
                    ->delete($thumbnailPath);
            }

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Failed to create document.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(
        CompanyDocument $companyDocument
    ) {
        return view(
            'admin.company-documents.show',
            [
                'document' => $companyDocument,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        CompanyDocument $companyDocument
    ) {
        return view(
            'admin.company-documents.edit', [
                'document' => $companyDocument,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        CompanyDocumentRequest $request,
        CompanyDocument $companyDocument
    ) {

        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Preserve Slug (regenerate only when the default-locale title changes)
        |--------------------------------------------------------------------------
        */

        $defaultLocale = config('locales.default');

        $defaultTitle = $validated['title_'.$defaultLocale];

        // Regenerate the slug only when enabled (config) AND the default-locale
        // title actually changed — keeps permalinks freezable after go-live.
        if (
            config('cms.auto_regenerate_slug', true)
            && $companyDocument->{'title_'.$defaultLocale} !== $defaultTitle
        ) {

            $validated['slug'] = $this->generateUniqueSlug(
                CompanyDocument::class,
                $defaultTitle,
                $companyDocument->id
            );
        }

        $oldFile = $companyDocument->file;
        $oldThumbnail = $companyDocument->thumbnail;

        $newFile = null;
        $newThumbnail = null;

        try {

            DB::beginTransaction();

            if (
                ($validated['document_type'] ?? null)
                === 'company_profile'
                &&
                $validated['status']
                === 'active'
            ) {

                CompanyDocument::where(
                    'document_type',
                    'company_profile'
                )
                    ->where(
                        'id',
                        '!=',
                        $companyDocument->id
                    )
                    ->update([
                        'status' => 'inactive',
                    ]);
            }

            if ($request->hasFile('file')) {

                $newFile =
                    $this->uploadFile(

                        $request->file('file'),

                        'company-documents',

                        $defaultTitle
                    );

                $validated['file'] =
                    $newFile;

                $validated['file_size'] =
                    $request->file('file')
                        ->getSize();
            }

            if (
                $request->hasFile('thumbnail')
            ) {

                $newThumbnail =
                    $this->uploadImage(

                        $request->file('thumbnail'),

                        'company-documents/thumbnails',

                        $defaultTitle
                    );

                $validated['thumbnail']
                    = $newThumbnail;
            }

            $companyDocument->update(
                $validated
            );

            DB::commit();

            if ($newFile && $oldFile) {

                Storage::disk('public')
                    ->delete($oldFile);
            }

            if (
                $newThumbnail
                &&
                $oldThumbnail
            ) {

                Storage::disk('public')
                    ->delete($oldThumbnail);
            }

            activity_log(

                'Company Document',

                'Updated document: '
                    .$companyDocument->title
            );

            return redirect()
                ->route(
                    'admin.company-documents.index'
                )
                ->with(
                    'success',
                    'Document updated successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Failed to update document.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        CompanyDocument $companyDocument
    ) {
        try {

            if ($companyDocument->trashed()) {

                return back()->with(
                    'error',
                    'Document is already in trash.'
                );
            }

            $companyDocument->delete();

            activity_log(
                'Company Document',
                'Moved document to trash: '.$companyDocument->title
            );

            return back()->with(
                'success',
                'Document moved to trash.'
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(
                'error',
                'Failed to delete document.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TRASH
    |--------------------------------------------------------------------------
    */

    public function trash()
    {
        $documents =
            CompanyDocument::onlyTrashed()
                ->latest()
                ->paginate(
                    self::PAGINATION
                );

        return view(
            'admin.company-documents.trash',
            compact('documents')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESTORE
    |--------------------------------------------------------------------------
    */

    public function restore(int $id)
    {
        $document =
            CompanyDocument::onlyTrashed()
                ->findOrFail($id);

        $document->restore();

        activity_log(
            'Company Document',
            'Restored document: '
                .$document->title
        );

        return back()->with(
            'success',
            'Document restored successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FORCE DELETE
    |--------------------------------------------------------------------------
    */

    public function forceDelete(int $id)
    {
        $document =
            CompanyDocument::onlyTrashed()
                ->findOrFail($id);

        if ($document->file) {

            Storage::disk('public')
                ->delete(
                    $document->file
                );
        }

        if ($document->thumbnail) {

            Storage::disk('public')
                ->delete(
                    $document->thumbnail
                );
        }

        activity_log(
            'Company Document',
            'Permanently deleted document: '
                .$document->title
        );

        $document->forceDelete();

        return back()->with(
            'success',
            'Document permanently deleted.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function uploadFile(
        UploadedFile $file,
        string $folder,
        string $name
    ): string {

        $filename =
            time()
            .'-'
            .Str::slug($name)
            .'.'
            .$file->getClientOriginalExtension();

        return $file->storeAs(
            $folder,
            $filename,
            'public'
        );
    }

    private function uploadImage(
        UploadedFile $image,
        string $folder,
        string $name
    ): string {

        $filename =
            time()
            .'-'
            .Str::slug($name)
            .'.'
            .$image->getClientOriginalExtension();

        return $image->storeAs(
            $folder,
            $filename,
            'public'
        );
    }
}
