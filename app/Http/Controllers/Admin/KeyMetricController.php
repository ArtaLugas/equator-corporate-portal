<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KeyMetric;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KeyMetricController extends Controller
{
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

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $validated['display_order'] ??= 0;

        try {
            $metric = KeyMetric::create($validated);
            activity_log('Key Metrics', 'Created metric: ' . $metric->label);

            return redirect()->route('admin.key-metrics.index')
                ->with('success', 'Metric created successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create metric.');
        }
    }

    public function edit(KeyMetric $keyMetric)
    {
        return view('admin.key-metrics.edit', compact('keyMetric'));
    }

    public function update(Request $request, KeyMetric $keyMetric)
    {
        $validated = $this->validateData($request);
        $validated['display_order'] ??= 0;

        try {
            $keyMetric->update($validated);
            activity_log('Key Metrics', 'Updated metric: ' . $keyMetric->label);

            return redirect()->route('admin.key-metrics.index')
                ->with('success', 'Metric updated successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update metric.');
        }
    }

    public function destroy(KeyMetric $keyMetric)
    {
        try {
            $label = $keyMetric->label;
            $keyMetric->delete();
            activity_log('Key Metrics', 'Deleted metric: ' . $label);

            return back()->with('success', 'Metric deleted successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Failed to delete metric.');
        }
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'icon' => ['nullable', 'string', 'max:100'],
            'value' => ['required', 'string', 'max:50'],
            'label' => ['required', 'string', 'max:191'],
            'display_order' => [
                'nullable', 'integer', 'min:0',
                Rule::unique('key_metrics', 'display_order')->ignore($request->route('key_metric')?->id),
            ],
            'status' => ['required', 'in:active,inactive'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        // Toggle mengirim '0'/'1' (hidden input) — normalisasi ke boolean.
        $validated['is_featured'] = $request->boolean('is_featured');

        return $validated;
    }
}
