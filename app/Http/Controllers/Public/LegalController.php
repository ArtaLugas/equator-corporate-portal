<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * Static legal pages (Privacy Policy, Cookie Policy). Content is bilingual and
 * version-controlled in lang/{locale}/legal.php — no database, admin-managed
 * CMS, or per-request data is involved.
 */
class LegalController extends Controller
{
    public function privacy(): View
    {
        return view('legal.privacy');
    }

    public function cookies(): View
    {
        return view('legal.cookies');
    }
}
