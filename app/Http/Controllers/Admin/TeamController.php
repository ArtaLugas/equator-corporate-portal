<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeamRequest;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TeamController extends Controller
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
        $query = Team::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                // name is single-language; position is translatable across locales.
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");

                foreach (array_keys(config('locales.supported', [])) as $locale) {
                    $q->orWhere("position_{$locale}", 'like', "%{$search}%");
                }
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

        $teams = $query
            ->paginate(self::PAGINATION)
            ->withQueryString();

        return view(
            'admin.teams.index',
            compact('teams')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('admin.teams.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(TeamRequest $request)
    {
        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Default Display Order
        |--------------------------------------------------------------------------
        */

        $validated['display_order'] ??= 1;

        $photoPath = null;

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Upload Photo
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('photo')) {

                $photoPath = $this->uploadImage(

                    $request->file('photo'),

                    'teams',

                    $validated['name']
                );

                $validated['photo'] = $photoPath;
            }

            /*
            |--------------------------------------------------------------------------
            | Create Team
            |--------------------------------------------------------------------------
            */

            $team = Team::create($validated);

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Team',

                'Created team member: '.$team->name
            );

            DB::commit();

            return redirect()
                ->route('admin.teams.index')
                ->with(
                    'success',
                    'Team member created successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            /*
            |--------------------------------------------------------------------------
            | Cleanup Uploaded Photo
            |--------------------------------------------------------------------------
            */

            if ($photoPath) {

                Storage::disk('public')
                    ->delete($photoPath);
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

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(Team $team)
    {
        return view(
            'admin.teams.show',
            compact('team')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(Team $team)
    {
        return view(
            'admin.teams.edit',
            compact('team')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        TeamRequest $request,
        Team $team
    ) {

        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Default Display Order
        |--------------------------------------------------------------------------
        */

        $validated['display_order'] ??= 1;

        $oldPhoto = $team->photo;

        $newPhoto = null;

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Upload New Photo
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('photo')) {

                $newPhoto = $this->uploadImage(

                    $request->file('photo'),

                    'teams',

                    $validated['name']
                );

                $validated['photo'] = $newPhoto;
            }

            /*
            |--------------------------------------------------------------------------
            | Remove Existing Photo
            |--------------------------------------------------------------------------
            */

            if (! $request->hasFile('photo') && $request->boolean('remove_image')) {

                $validated['photo'] = null;
            }

            /*
            |--------------------------------------------------------------------------
            | Update Team
            |--------------------------------------------------------------------------
            */

            $team->update($validated);

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Delete Old Photo (replaced)
            |--------------------------------------------------------------------------
            */

            if ($newPhoto && $oldPhoto) {

                Storage::disk('public')
                    ->delete($oldPhoto);
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Old Photo (removed)
            |--------------------------------------------------------------------------
            */

            if (
                $request->boolean('remove_image')
                && $oldPhoto
            ) {

                Storage::disk('public')
                    ->delete($oldPhoto);
            }

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Team',

                'Updated team member: '.$team->name
            );

            return redirect()
                ->to(guarded_list_url($request->input('return_url'), route('admin.teams.index')))
                ->with(
                    'success',
                    'Team member updated successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            /*
            |--------------------------------------------------------------------------
            | Cleanup Uploaded Photo
            |--------------------------------------------------------------------------
            */

            if ($newPhoto) {

                Storage::disk('public')
                    ->delete($newPhoto);
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

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(Team $team)
    {
        try {

            DB::transaction(function () use ($team) {

                $team->delete();
            });

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Team',

                'Moved team member to trash: '.$team->name
            );

            return back()->with(

                'success',

                'Team member moved to trash.'
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(

                'error',

                friendly_error($e)
            );
        }
    }

    public function bulkDestroy()
    {
        $ids = array_map('intval', (array) request()->input('ids', []));

        if (empty($ids)) {
            return back()->with('error', __('flash.none_selected'));
        }

        $count = Team::whereIn('id', $ids)->get()->each->delete()->count();

        activity_log('Team', "Bulk moved {$count} member(s) to trash.");

        return back()->with('success', "{$count} member(s) moved to trash.");
    }

    /*
    |--------------------------------------------------------------------------
    | TRASH
    |--------------------------------------------------------------------------
    */

    public function trash()
    {
        $teams = Team::onlyTrashed()

            ->latest()

            ->paginate(self::PAGINATION);

        return view(
            'admin.teams.trash',
            compact('teams')
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

            $team = DB::transaction(function () use ($id) {

                $team = Team::onlyTrashed()
                    ->findOrFail($id);

                $team->restore();

                return $team;
            });

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Team',

                'Restored team member: '.$team->name
            );

            return back()->with(

                'success',

                'Team member restored successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(

                'error',

                friendly_error($e)
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
        $photoPath = null;

        try {

            $team = DB::transaction(function () use (

                $id,

                &$photoPath

            ) {

                $team = Team::onlyTrashed()
                    ->findOrFail($id);

                $photoPath = $team->photo;

                $team->forceDelete();

                return $team;
            });

            /*
            |--------------------------------------------------------------------------
            | Delete Photo
            |--------------------------------------------------------------------------
            */

            if ($photoPath) {

                Storage::disk('public')
                    ->delete($photoPath);
            }

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            activity_log(

                'Team',

                'Permanently deleted team member: '.$team->name
            );

            return back()->with(

                'success',

                'Team member permanently deleted.'
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(

                'error',

                friendly_error($e)
            );
        }
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
            time().
            '-'.
            Str::slug($name).
            '.'.
            $image->getClientOriginalExtension();

        return $image->storeAs(
            $folder,
            $filename,
            'public'
        );
    }
}
