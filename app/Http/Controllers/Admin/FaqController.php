<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FaqController extends Controller
{
    private const PAGINATION = 15;

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Faq::query()->search($request->search);

        switch ($request->sort) {
            case 'newest':
                $query->latest();
                break;
            case 'oldest':
                $query->oldest();
                break;
            default:
                $query->orderBy('display_order')->orderBy('id');
                break;
        }

        $faqs = $query->paginate(self::PAGINATION)->withQueryString();

        return view('admin.faqs.index', compact('faqs'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE / STORE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('admin.faqs.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        $validated['display_order'] ??= 0;

        try {
            $faq = Faq::create($validated);

            activity_log('FAQ', 'Created FAQ: #' . $faq->id);

            return redirect()
                ->route('admin.faqs.index')
                ->with('success', 'FAQ created successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create FAQ.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT / UPDATE
    |--------------------------------------------------------------------------
    */

    public function edit(Faq $faq)
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $validated = $this->validateData($request);

        $validated['display_order'] ??= 0;

        try {
            $faq->update($validated);

            activity_log('FAQ', 'Updated FAQ: #' . $faq->id);

            return redirect()
                ->route('admin.faqs.index')
                ->with('success', 'FAQ updated successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update FAQ.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(Faq $faq)
    {
        try {
            $id = $faq->id;
            $faq->delete();

            activity_log('FAQ', 'Deleted FAQ: #' . $id);

            return back()->with('success', 'FAQ deleted successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Failed to delete FAQ.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Validate
    |--------------------------------------------------------------------------
    */

    private function validateData(Request $request): array
    {
        return $request->validate([
            'question' => ['required', 'string', 'max:1000'],
            'answer' => ['required', 'string', 'max:10000'],
            'display_order' => [
                'nullable', 'integer', 'min:0',
                Rule::unique('faqs', 'display_order')->ignore($request->route('faq')?->id),
            ],
        ]);
    }
}
