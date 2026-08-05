<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AuthorizesModuleActions;
use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Validation\Rule;

class SocialLinkController extends Controller implements HasMiddleware
{
    use AuthorizesModuleActions;

    public static function middleware(): array
    {
        return static::moduleMiddleware('social-link');
    }

    private const PAGINATION = 15;

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = SocialLink::query()
            ->search($request->search)
            ->status($request->status);

        switch ($request->sort) {
            case 'newest':
                $query->latest();
                break;
            case 'platform':
                $query->orderBy('platform');
                break;
            default:
                $query->orderBy('display_order')->orderBy('id');
                break;
        }

        $socialLinks = $query->paginate(self::PAGINATION)->withQueryString();

        return view('admin.social-links.index', compact('socialLinks'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE / STORE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('admin.social-links.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        $validated['display_order'] ??= 0;

        try {
            $link = SocialLink::create($validated);

            activity_log('Social Links', 'Created social link: '.$link->platform);

            return redirect()
                ->route('admin.social-links.index')
                ->with('success', 'Social link created successfully.');
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

    public function edit(SocialLink $socialLink)
    {
        return view('admin.social-links.edit', compact('socialLink'));
    }

    public function update(Request $request, SocialLink $socialLink)
    {
        $validated = $this->validateData($request);

        $validated['display_order'] ??= 0;

        try {
            $socialLink->update($validated);

            activity_log('Social Links', 'Updated social link: '.$socialLink->platform);

            return redirect()
                ->to(guarded_list_url($request->input('return_url'), route('admin.social-links.index')))
                ->with('success', 'Social link updated successfully.');
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

    public function destroy(SocialLink $socialLink)
    {
        try {
            $platform = $socialLink->platform;
            $socialLink->delete();

            activity_log('Social Links', 'Deleted social link: '.$platform);

            return back()->with('success', 'Social link deleted successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', friendly_error($e));
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
            'platform' => ['required', 'string', 'max:50'],
            'url' => ['required', 'url', 'max:500'],
            'icon_class' => ['nullable', 'string', 'max:100'],
            'display_order' => [
                'nullable', 'integer', 'min:0',
                Rule::unique('social_links', 'display_order')->ignore($request->route('social_link')?->id),
            ],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
