<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
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
        $query = Partner::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('website', 'like', "%{$search}%");
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

        $partners = $query
            ->paginate(self::PAGINATION)
            ->withQueryString();

        return view(
            'admin.partners.index',
            compact('partners')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('admin.partners.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        /*
        |--------------------------------------------------------------------------
        | Default Display Order
        |--------------------------------------------------------------------------
        */

        $validated['display_order'] ??= 1;

        $logoPath = null;

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Upload Logo
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('logo')) {

                $logoPath = $this->uploadImage(

                    $request->file('logo'),

                    'partners',

                    $validated['name']
                );

                $validated['logo'] = $logoPath;
            }

            /*
            |--------------------------------------------------------------------------
            | Create Partner
            |--------------------------------------------------------------------------
            */

            $partner = Partner::create($validated);

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Partner',

                'Created partner: ' . $partner->name
            );

            DB::commit();

            return redirect()
                ->route('admin.partners.index')
                ->with(
                    'success',
                    'Partner created successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            /*
            |--------------------------------------------------------------------------
            | Cleanup Uploaded Logo
            |--------------------------------------------------------------------------
            */

            if ($logoPath) {

                Storage::disk('public')
                    ->delete($logoPath);
            }

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Failed to create partner.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(Partner $partner)
    {
        return view(
            'admin.partners.show',
            compact('partner')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(Partner $partner)
    {
        return view(
            'admin.partners.edit',
            compact('partner')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Partner $partner
    ) {

        $validated = $this->validateData($request, $partner);

        /*
        |--------------------------------------------------------------------------
        | Default Display Order
        |--------------------------------------------------------------------------
        */

        $validated['display_order'] ??= 1;

        $oldLogo = $partner->logo;

        $newLogo = null;

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Upload New Logo
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('logo')) {

                $newLogo = $this->uploadImage(

                    $request->file('logo'),

                    'partners',

                    $validated['name']
                );

                $validated['logo'] = $newLogo;
            }

            /*
            |--------------------------------------------------------------------------
            | Remove Existing Logo
            |--------------------------------------------------------------------------
            */

            if (! $request->hasFile('logo') && $request->boolean('remove_image')) {

                $validated['logo'] = null;
            }

            /*
            |--------------------------------------------------------------------------
            | Update Partner
            |--------------------------------------------------------------------------
            */

            $partner->update($validated);

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Delete Old Logo (replaced)
            |--------------------------------------------------------------------------
            */

            if ($newLogo && $oldLogo) {

                Storage::disk('public')
                    ->delete($oldLogo);
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Old Logo (removed)
            |--------------------------------------------------------------------------
            */

            if (
                $request->boolean('remove_image')
                && $oldLogo
            ) {

                Storage::disk('public')
                    ->delete($oldLogo);
            }

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Partner',

                'Updated partner: ' . $partner->name
            );

            return redirect()
                ->route('admin.partners.index')
                ->with(
                    'success',
                    'Partner updated successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            /*
            |--------------------------------------------------------------------------
            | Cleanup Uploaded Logo
            |--------------------------------------------------------------------------
            */

            if ($newLogo) {

                Storage::disk('public')
                    ->delete($newLogo);
            }

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Failed to update partner.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(Partner $partner)
    {
        try {

            DB::transaction(function () use ($partner) {

                $partner->delete();
            });

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Partner',

                'Moved partner to trash: ' . $partner->name
            );

            return back()->with(

                'success',

                'Partner moved to trash.'
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(

                'error',

                'Failed to delete partner.'
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
        $partners = Partner::onlyTrashed()

            ->latest()

            ->paginate(self::PAGINATION);

        return view(
            'admin.partners.trash',
            compact('partners')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESTORE
    |--------------------------------------------------------------------------
    */

    public function restore(int $id)
    {
        try {

            $partner = DB::transaction(function () use ($id) {

                $partner = Partner::onlyTrashed()
                    ->findOrFail($id);

                $partner->restore();

                return $partner;
            });

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Partner',

                'Restored partner: ' . $partner->name
            );

            return back()->with(

                'success',

                'Partner restored successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(

                'error',

                'Failed to restore partner.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FORCE DELETE
    |--------------------------------------------------------------------------
    */

    public function forceDelete(int $id)
    {
        $logoPath = null;

        try {

            $partner = DB::transaction(function () use (

                $id,

                &$logoPath

            ) {

                $partner = Partner::onlyTrashed()
                    ->findOrFail($id);

                $logoPath = $partner->logo;

                $partner->forceDelete();

                return $partner;
            });

            /*
            |--------------------------------------------------------------------------
            | Delete Logo
            |--------------------------------------------------------------------------
            */

            if ($logoPath) {

                Storage::disk('public')
                    ->delete($logoPath);
            }

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Partner',

                'Permanently deleted partner: ' . $partner->name
            );

            return back()->with(

                'success',

                'Partner permanently deleted.'
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(

                'error',

                'Failed to permanently delete partner.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Data
    |--------------------------------------------------------------------------
    */

    private function validateData(Request $request, ?Partner $partner = null): array
    {
        return $request->validate([

            'name' => [
                'required',
                'string',
                'max:191',
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,svg',
                'max:2048',
            ],

            'website' => [
                'nullable',
                'url',
                'max:500',
            ],

            'display_order' => [
                'nullable',
                'integer',
                'min:1',

                // Display order must be unique per table.
                // Soft-deleted rows are excluded so their order can be reused.
                Rule::unique('partners', 'display_order')
                    ->ignore($partner?->id)
                    ->whereNull('deleted_at'),
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ], [
            'display_order.unique' => 'This display order is already used by another partner.',
        ]);
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

        $filename =
            time() .
            '-' .
            Str::slug($name) .
            '.' .
            $image->getClientOriginalExtension();

        return $image->storeAs(
            $folder,
            $filename,
            'public'
        );
    }
}
