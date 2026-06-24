<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\TranslationProgress;

/**
 * Read-only overview of translation completeness per content module, so editors
 * can see how close each module is to being publishable in the non-default
 * locale. Does not change any data or fallback behaviour.
 */
class TranslationProgressController extends Controller
{
    public function index()
    {
        $locale = TranslationProgress::firstNonDefaultLocale();
        $rows = TranslationProgress::forLocale($locale);

        return view('admin.translations.index', [
            'rows' => $rows,
            'locale' => $locale,
            'overall' => TranslationProgress::overallPercent($rows),
            'complete' => TranslationProgress::isComplete($rows),
        ]);
    }
}
