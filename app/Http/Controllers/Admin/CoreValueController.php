<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CoreValueRequest;
use App\Models\CoreValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoreValueController extends Controller
{
    private const PAGINATION = 10;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CoreValue::query();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                foreach (array_keys(config('locales.supported', [])) as $locale) {
                    $q->orWhere("title_{$locale}", 'like', "%{$search}%")
                        ->orWhere("description_{$locale}", 'like', "%{$search}%");
                }
                $q->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

            default:
                $query->latest();
                break;
        }

        $coreValues = $query
            ->paginate(self::PAGINATION)
            ->withQueryString();

        return view(
            'admin.core-values.index',
            compact('coreValues')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.core-values.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CoreValueRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $coreValue = CoreValue::create($validated);

            activity_log(
                'Core Value',
                'Created core value: '.$coreValue->title
            );
        });

        return redirect()
            ->route('admin.core-values.index')
            ->with('success', 'Core value created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CoreValue $coreValue)
    {
        return view(
            'admin.core-values.show',
            compact('coreValue')
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CoreValue $coreValue)
    {
        return view(
            'admin.core-values.edit',
            compact('coreValue')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CoreValueRequest $request, CoreValue $coreValue)
    {
        $validated = $request->validated();

        DB::transaction(function () use (
            $validated,
            $coreValue
        ) {

            $coreValue->update($validated);

            activity_log(
                'Core Value',
                'Updated core value: '.$coreValue->title
            );
        });

        return redirect()
            ->route('admin.core-values.index')
            ->with(
                'success',
                'Core value updated successfully.'
            );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        CoreValue $coreValue
    ) {

        DB::transaction(function () use (
            $coreValue
        ) {

            $title = $coreValue->title;

            $coreValue->delete();

            activity_log(
                'Core Value',
                'Deleted core value: '.$title
            );
        });

        return redirect()
            ->route('admin.core-values.index')
            ->with(
                'success',
                'Core value deleted successfully.'
            );
    }
}
