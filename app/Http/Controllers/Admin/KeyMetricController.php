<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AuthorizesModuleActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KeyMetricRequest;
use App\Models\KeyMetric;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class KeyMetricController extends Controller implements HasMiddleware
{
    use AuthorizesModuleActions;

    public static function middleware(): array
    {
        return static::moduleMiddleware('key-metric');
    }

    private const PAGINATION = 15;

    public function index(Request $request)
    {
        $query = KeyMetric::query()->search($request->search);

        if (in_array($request->status, ['active', 'inactive'], true)) {
            $query->where('status', $request->status);
        }

        if (in_array($request->featured, ['1', '0'], true)) {
            $query->where('is_featured', $request->featured === '1');
        }

        switch ($request->sort) {
            case 'newest':
                $query->latest();
                break;
            default:
                $query->orderBy('display_order')->orderBy('id');
                break;
        }

        $metrics = $query->paginate(self::PAGINATION)->withQueryString();

        return view('admin.key-metrics.index', compact('metrics'));
    }

    public function create()
    {
        return view('admin.key-metrics.create');
    }

    public function store(KeyMetricRequest $request)
    {
        $validated = $request->validated();
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['display_order'] ??= 0;

        try {
            $metric = KeyMetric::create($validated);
            activity_log('Key Metrics', 'Created metric: '.$metric->label);

            return redirect()->route('admin.key-metrics.index')
                ->with('success', 'Metric created successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', friendly_error($e));
        }
    }

    public function edit(KeyMetric $keyMetric)
    {
        return view('admin.key-metrics.edit', compact('keyMetric'));
    }

    public function update(KeyMetricRequest $request, KeyMetric $keyMetric)
    {
        $validated = $request->validated();
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['display_order'] ??= 0;

        try {
            $keyMetric->update($validated);
            activity_log('Key Metrics', 'Updated metric: '.$keyMetric->label);

            return redirect()->to(guarded_list_url($request->input('return_url'), route('admin.key-metrics.index')))
                ->with('success', 'Metric updated successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', friendly_error($e));
        }
    }

    public function destroy(KeyMetric $keyMetric)
    {
        try {
            $label = $keyMetric->label;
            $keyMetric->delete();
            activity_log('Key Metrics', 'Deleted metric: '.$label);

            return back()->with('success', 'Metric deleted successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', friendly_error($e));
        }
    }
}
