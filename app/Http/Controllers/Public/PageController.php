<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AboutHistory;
use App\Models\AboutSection;
use App\Models\CompanyDocument;
use App\Models\CoreValue;
use App\Models\Faq;
use App\Models\Team;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    /** Cache key for the About payload (invalidated by HomeContentCacheObserver). */
    public const ABOUT_CACHE_KEY = 'public.about.data';

    public function about()
    {
        // About is a heavy, rarely-changing page — cache the whole payload and
        // let the content observer bust it on save/delete (TTL is a safety net).
        $data = Cache::remember(self::ABOUT_CACHE_KEY, now()->addHour(), fn () => $this->buildAboutData());

        return view('public.about', $data);
    }

    private function buildAboutData(): array
    {
        $sections = AboutSection::where('status', 'active')
            ->with(['contents' => fn ($q) => $q->where('status', 'active')->orderBy('display_order')])
            ->orderBy('display_order')->get();

        $coreValues = CoreValue::where('status', 'active')->orderBy('display_order')->get();

        // Chronological (oldest → newest) so the "journey" timeline reads forward.
        $histories = AboutHistory::where('status', 'active')
            ->orderBy('year')->orderBy('display_order')->get();

        $teams = Team::where('status', 'active')->orderBy('display_order')->get();

        $companyProfile = CompanyDocument::where('status', 'active')
            ->where('document_type', 'company_profile')
            ->orderBy('display_order')->first();

        return compact('sections', 'coreValues', 'histories', 'teams', 'companyProfile');
    }

    public function faq()
    {
        $faqs = Faq::orderBy('display_order')->orderBy('id')->get();

        return view('public.faq', compact('faqs'));
    }
}
