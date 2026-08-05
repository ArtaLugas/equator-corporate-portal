<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AuthorizesModuleActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OfficeLocationRequest;
use App\Models\OfficeLocation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class OfficeLocationController extends Controller implements HasMiddleware
{
    use AuthorizesModuleActions;

    public static function middleware(): array
    {
        return static::moduleMiddleware('office-location');
    }

    private const PAGINATION = 15;

    public function index(Request $request)
    {
        $query = OfficeLocation::query()
            ->search($request->search)
            ->status($request->status);

        switch ($request->sort) {
            case 'newest':
                $query->latest();
                break;
            case 'name':
                $query->orderBy('name_'.config('locales.default'));
                break;
            default:
                $query->ordered();
                break;
        }

        $locations = $query->paginate(self::PAGINATION)->withQueryString();

        return view('admin.office-locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.office-locations.create');
    }

    public function store(OfficeLocationRequest $request)
    {
        $validated = $request->validated();
        $validated['display_order'] ??= 0;
        $validated['is_primary'] = $request->boolean('is_primary');

        try {
            $location = OfficeLocation::create($validated);
            $this->syncPrimary($location);

            activity_log('Office Locations', 'Created office location: '.$location->name);

            return redirect()->route('admin.office-locations.index')
                ->with('success', 'Office location created successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', friendly_error($e));
        }
    }

    public function edit(OfficeLocation $officeLocation)
    {
        return view('admin.office-locations.edit', ['location' => $officeLocation]);
    }

    public function update(OfficeLocationRequest $request, OfficeLocation $officeLocation)
    {
        $validated = $request->validated();
        $validated['display_order'] ??= 0;
        $validated['is_primary'] = $request->boolean('is_primary');

        try {
            $officeLocation->update($validated);
            $this->syncPrimary($officeLocation);

            activity_log('Office Locations', 'Updated office location: '.$officeLocation->name);

            return redirect()->to(guarded_list_url($request->input('return_url'), route('admin.office-locations.index')))
                ->with('success', 'Office location updated successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', friendly_error($e));
        }
    }

    public function destroy(OfficeLocation $officeLocation)
    {
        try {
            $name = $officeLocation->name;
            $officeLocation->delete();

            activity_log('Office Locations', 'Deleted office location: '.$name);

            return back()->with('success', 'Office location deleted successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', friendly_error($e));
        }
    }

    /**
     * Pastikan hanya ada SATU lokasi primary.
     */
    private function syncPrimary(OfficeLocation $location): void
    {
        if ($location->is_primary) {
            OfficeLocation::where('id', '!=', $location->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }
    }
}
