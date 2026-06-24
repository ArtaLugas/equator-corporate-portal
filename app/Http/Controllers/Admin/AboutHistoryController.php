<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AboutHistoryRequest;
use App\Models\AboutHistory;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
                $q->whereRaw('CAST(year AS CHAR) LIKE ?', ["%{$search}%"]);

                // Title is translatable — search every locale column.
                foreach (array_keys(config('locales.supported', [])) as $locale) {
                    $q->orWhere("title_{$locale}", 'like', "%{$search}%");
                }
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
    public function store(AboutHistoryRequest $request)
    {
        $validated = $request->validated();

        $imagePath = null;

        try {

            DB::beginTransaction();

            if ($request->hasFile('image')) {

                $imagePath = $this->uploadImage(

                    $request->file('image'),

                    'about-histories',

                    $validated['title_en']
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
                    friendly_error($e)
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
    public function update(AboutHistoryRequest $request, AboutHistory $aboutHistory)
    {
        $validated = $request->validated();

        $oldImage = $aboutHistory->image;

        $newImage = null;

        try {

            DB::beginTransaction();

            if ($request->hasFile('image')) {

                $newImage = $this->uploadImage(

                    $request->file('image'),

                    'about-histories',

                    $validated['title_en']
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
                ->to(guarded_list_url($request->input('return_url'), route('admin.about-histories.index')))
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
                    friendly_error($e)
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
                friendly_error($e)
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
