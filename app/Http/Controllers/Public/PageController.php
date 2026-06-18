<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AboutHistory;
use App\Models\AboutSection;
use App\Models\CompanyDocument;
use App\Models\CoreValue;
use App\Models\Faq;
use App\Models\KeyMetric;
use App\Models\Partner;
use App\Models\Team;

class PageController extends Controller
{
    public function about()
    {
        $sections = AboutSection::where('status', 'active')
            ->with(['contents' => fn ($q) => $q->where('status', 'active')->orderBy('display_order')])
            ->orderBy('display_order')->get();

        $coreValues = CoreValue::where('status', 'active')->orderBy('display_order')->get();

        $histories = AboutHistory::where('status', 'active')->orderByDesc('year')->orderBy('display_order')->get();

        $teams = Team::where('status', 'active')->orderBy('display_order')->get();

        $partners = Partner::where('status', 'active')->orderBy('display_order')->get();

        $keyMetrics = KeyMetric::where('status', 'active')->orderBy('display_order')->get();

        $companyProfile = CompanyDocument::where('status', 'active')
            ->where('document_type', 'company_profile')
            ->orderBy('display_order')->first();

        return view('public.about', compact('sections', 'coreValues', 'histories', 'teams', 'partners', 'keyMetrics', 'companyProfile'));
    }

    public function faq()
    {
        $faqs = Faq::orderBy('display_order')->orderBy('id')->get();

        return view('public.faq', compact('faqs'));
    }
}
