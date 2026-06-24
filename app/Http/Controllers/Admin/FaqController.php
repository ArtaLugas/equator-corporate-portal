<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Models\Faq;
use Illuminate\Http\Request;

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

    public function store(FaqRequest $request)
    {
        $validated = $request->validated();

        $validated['display_order'] ??= 0;

        try {
            $faq = Faq::create($validated);

            activity_log('FAQ', 'Created FAQ: #'.$faq->id);

            return redirect()
                ->route('admin.faqs.index')
                ->with('success', 'FAQ created successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', friendly_error($e));
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

    public function update(FaqRequest $request, Faq $faq)
    {
        $validated = $request->validated();

        $validated['display_order'] ??= 0;

        try {
            $faq->update($validated);

            activity_log('FAQ', 'Updated FAQ: #'.$faq->id);

            return redirect()
                ->to(guarded_list_url($request->input('return_url'), route('admin.faqs.index')))
                ->with('success', 'FAQ updated successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', friendly_error($e));
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

            activity_log('FAQ', 'Deleted FAQ: #'.$id);

            return back()->with('success', 'FAQ deleted successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', friendly_error($e));
        }
    }
}
