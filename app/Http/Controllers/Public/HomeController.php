<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AboutSection;
use App\Models\CompanyDocument;
use App\Models\CoreValue;
use App\Models\HeroBanner;
use App\Models\KeyMetric;
use App\Models\Partner;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /** Cache key untuk payload homepage (di-invalidate lewat HomeContentCache observer). */
    public const CACHE_KEY = 'public.home.data';

    public function index()
    {
        // Homepage = halaman tersibuk dengan data CMS yang jarang berubah.
        // Cache seluruh payload; di-bust otomatis saat konten terkait disimpan/dihapus
        // (lihat App\Observers\HomeContentCacheObserver), dengan TTL pengaman 1 jam.
        $data = Cache::remember(self::CACHE_KEY, now()->addHour(), fn () => $this->buildPageData());

        return view('public.home', $data);
    }

    /**
     * Kumpulkan seluruh data yang dibutuhkan homepage.
     */
    private function buildPageData(): array
    {
        $heroBanners = HeroBanner::where('status', 'active')
            ->orderBy('display_order')->get();

        $keyMetrics = KeyMetric::where('status', 'active')
            ->orderBy('display_order')->get();

        // $keyMetrics sudah difilter active → cukup filter is_featured di memori.
        $featuredMetric = $keyMetrics->firstWhere('is_featured', true);

        // Services preview: utamakan featured; bila < limit, lengkapi dgn terbaru lain.
        $featuredServices = $this->featuredServices(4);

        $featuredProjects = Project::with('services:id,name')
            ->orderByDesc('is_featured')
            ->latest()->take(6)->get();

        $coreValues = CoreValue::where('status', 'active')->orderBy('display_order')->get();

        $partners = Partner::where('status', 'active')->orderBy('display_order')->get();

        // About: muat semua section aktif + kontennya (anti N+1), lalu ratakan.
        $aboutSections = AboutSection::where('status', 'active')
            ->with(['contents' => fn ($q) => $q->where('status', 'active')->orderBy('display_order')])
            ->orderBy('display_order')
            ->get();

        $aboutSection = $aboutSections->first();
        $aboutContents = $aboutSections->flatMap->contents->keyBy('key');

        $companyProfilePath = CompanyDocument::where('status', 'active')
            ->orderBy('display_order')->first()?->file;

        return compact(
            'heroBanners',
            'keyMetrics',
            'featuredMetric',
            'featuredServices',
            'featuredProjects',
            'coreValues',
            'partners',
            'aboutSection',
            'aboutContents',
            'companyProfilePath',
        );
    }

    /**
     * Featured services dulu; bila kurang dari $limit, lengkapi dengan terbaru lain.
     */
    private function featuredServices(int $limit)
    {
        $featured = Service::where('status', 'published')
            ->where('is_featured', true)
            ->with('category')
            ->latest()
            ->take($limit)
            ->get();

        if ($featured->count() >= $limit) {
            return $featured;
        }

        $fill = Service::where('status', 'published')
            ->where('is_featured', false)
            ->whereNotIn('id', $featured->modelKeys())
            ->with('category')
            ->latest()
            ->take($limit - $featured->count())
            ->get();

        return $featured->concat($fill);
    }
}
