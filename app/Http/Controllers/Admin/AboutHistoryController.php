<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutHistory;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AboutHistoryController extends Controller
{
    /*
    |----------------------------------------------------------------------
    | Pagination
    |----------------------------------------------------------------------
    */

    private const PAGINATION = 10;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AboutHistory::query();

        /*
        |----------------------------------------------------------------------
        | Search
        |----------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->whereRaw('CAST(year AS CHAR) LIKE ?', ["%{$search}%"])
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        /*
        |----------------------------------------------------------------------
        | Status Filter
        |----------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        /*
        |-------------------------------------------------------------
        | Sorting
        |-------------------------------------------------------------
        */

        switch ($request->sort) {

            case 'oldest':
                $query->oldest();
                break;

            case 'year_asc':
                $query->orderBy('year');
                break;

            case 'year_desc':
                $query->orderByDesc('year');
                break;

            case 'display_order':
                $query->orderBy('display_order');
                break;

            default:
                $query->latest();
                break;
        }

        $histories = $query->paginate(self::PAGINATION)->withQueryString();

        return view('admin.about-histories.index', compact('histories'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.about-histories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = Validator::make(

            $request->all(),

            [
                'year' => [
                    'required',
                    'integer',
                    'min:1900',
                    'max:'.(date('Y') + 10),
                ],

                'title' => [
                    'required',
                    'string',
                    'max:191',
                ],

                'description' => [
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
                    'unique:about_histories,display_order',
                ],

                'status' => [
                    'required',
                    Rule::in([
                        'active',
                        'inactive',
                    ]),
                ],
            ]

        )->validate();

        $imagePath = null;

        try {

            DB::beginTransaction();

            if ($request->hasFile('image')) {

                $imagePath = $this->uploadImage(

                    $request->file('image'),

                    'about-histories',

                    $validated['title']
                );

                $validated['image'] = $imagePath;
            }

            $history = AboutHistory::create($validated);

            activity_log(

                'About History',

                'Created history: '.$history->title
            );

            DB::commit();

            return redirect()
                ->route('admin.about-histories.index')
                ->with(
                    'success',
                    'History created successfully.'
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
                    'Failed to create history.'
                );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AboutHistory $aboutHistory)
    {
        return view('admin.about-histories.show', compact('aboutHistory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AboutHistory $aboutHistory)
    {
        return view('admin.about-histories.edit', compact('aboutHistory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AboutHistory $aboutHistory)
    {
        $validated = Validator::make(

            $request->all(),

            [
                'year' => [
                    'required',
                    'integer',
                    'min:1900',
                    'max:'.(date('Y') + 10),
                ],

                'title' => [
                    'required',
                    'string',
                    'max:191',
                ],

                'description' => [
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
                    Rule::unique(
                        'about_histories',
                        'display_order'
                    )->ignore($aboutHistory->id),
                ],

                'status' => [
                    'required',
                    Rule::in([
                        'active',
                        'inactive',
                    ]),
                ],
            ]

        )->validate();

        $oldImage = $aboutHistory->image;

        $newImage = null;

        try {

            DB::beginTransaction();

            if ($request->hasFile('image')) {

                $newImage = $this->uploadImage(

                    $request->file('image'),

                    'about-histories',

                    $validated['title']
                );

                $validated['image'] = $newImage;
            }

            if (! $request->hasFile('image') && $request->boolean('remove_image')) {

                $validated['image'] = null;
            }

            $aboutHistory->update($validated);

            DB::commit();

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

            activity_log(

                'About History',

                'Updated history: '.$aboutHistory->title
            );

            return redirect()
                ->route('admin.about-histories.index')
                ->with(
                    'success',
                    'History updated successfully.'
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
                    'Failed to update history.'
                );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AboutHistory $aboutHistory)
    {
        try {

            DB::beginTransaction();

            $title = $aboutHistory->title;

            $aboutHistory->delete();

            activity_log(

                'About History',

                'Deleted history: '.$title
            );

            DB::commit();

            return redirect()
                ->route('admin.about-histories.index')
                ->with(
                    'success',
                    'History deleted successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()->with(
                'error',
                'Failed to delete history.'
            );
        }
    }

    /**
     * Upload image.
     */
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
