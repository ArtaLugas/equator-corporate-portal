<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AboutSection;
use App\Models\CompanyCredential;
use App\Models\CompanyDocument;
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

        // Stat tiles disusun DI LUAR cache: `label` KeyMetric bersifat translatable,
        // sedangkan payload di-cache satu kali (lintas-locale). Membangunnya per-request
        // dari $keyMetrics — objek model yang me-resolve locale secara lazy — menjaga
        // label mengikuti locale pembaca, bukan locale yang pertama mengisi cache (H-02.1).
        $data['stats'] = $this->buildStats($data['keyMetrics']);

        return view('public.home', $data);
    }

    /**
     * Kumpulkan seluruh data yang dibutuhkan homepage.
     */
    private function buildPageData(): array
    {
        $heroBanners = HeroBanner::active()->ordered()->get();

        $keyMetrics = KeyMetric::active()->orderBy('display_order')->get();

        // Services preview: utamakan featured; bila < limit, lengkapi dgn terbaru lain.
        $featuredServices = $this->featuredServices(4);

        $featuredProjects = Project::public()->with('services:id,name')
            ->orderByDesc('is_featured')
            ->latest()->take(6)->get();

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

        // Trusted Credentials: featured credentials for the homepage trust band.
        $featuredCredentials = CompanyCredential::active()->featured()->ordered()->take(6)->get();

        return compact(
            'heroBanners',
            'keyMetrics',
            'featuredServices',
            'featuredProjects',
            'partners',
            'aboutSection',
            'aboutContents',
            'companyProfilePath',
            'featuredCredentials',
        );
    }

    /**
     * Stat tiles: gunakan metrik CMS bila ada, jatuh ke default yang masuk akal.
     * Dibangun per-request (DI LUAR cache) karena `label` translatable — lihat index().
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\KeyMetric>  $keyMetrics
     */
    private function buildStats($keyMetrics): array
    {
        return $keyMetrics->isNotEmpty()
            ? $keyMetrics->map(fn ($m) => ['value' => $m->value, 'label' => $m->label])->all()
            : [
                ['value' => '15+', 'label' => __('home.stat_fallback_experience')],
                ['value' => '200+', 'label' => __('home.stat_fallback_projects')],
                ['value' => '50+', 'label' => __('home.stat_fallback_consultants')],
                ['value' => '6', 'label' => __('home.stat_fallback_countries')],
            ];
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
