<?php

namespace Tests\Feature;

use App\Models\KeyMetric;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_load(): void
    {
        foreach (['home', 'about', 'faq', 'services.index', 'projects.index', 'news.index', 'contact'] as $name) {
            $this->get(route($name))->assertOk();
        }
    }

    public function test_service_detail_loads(): void
    {
        $cat = ServiceCategory::create(['name' => 'Cat', 'slug' => 'cat', 'status' => 'active', 'display_order' => 1]);
        $service = Service::create([
            'category_id' => $cat->id, 'name_en' => 'My Service', 'slug' => 'my-service',
            'description_en' => '<p>Detail</p>', 'status' => 'published', 'is_featured' => false,
        ]);

        $this->get(route('services.show', $service->slug))->assertOk()->assertSee('My Service');
    }

    public function test_draft_service_returns_404(): void
    {
        $cat = ServiceCategory::create(['name' => 'Cat', 'slug' => 'cat', 'status' => 'active', 'display_order' => 1]);
        $service = Service::create([
            'category_id' => $cat->id, 'name_en' => 'Hidden', 'slug' => 'hidden', 'status' => 'draft', 'is_featured' => false,
        ]);

        $this->get(route('services.show', $service->slug))->assertNotFound();
    }

    public function test_project_detail_loads(): void
    {
        $project = Project::create([
            'name_en' => 'Big Project', 'slug' => 'big-project', 'status' => 'completed', 'is_featured' => false,
        ]);

        $this->get(route('projects.show', $project->slug))->assertOk()->assertSee('Big Project');
    }

    public function test_news_detail_loads_and_increments_views(): void
    {
        $article = $this->publishedArticle();

        // A real browser (sends a User-Agent) counts exactly once.
        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'])
            ->get(route('news.show', $article->slug))
            ->assertOk()->assertSee('Breaking');

        $this->assertSame(1, $article->fresh()->views_count);
    }

    /** R9: bots/crawlers (and empty User-Agent) must NOT inflate views_count. */
    public function test_news_view_not_counted_for_bots(): void
    {
        $article = $this->publishedArticle();

        $this->withHeaders(['User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'])
            ->get(route('news.show', $article->slug))->assertOk();

        $this->assertSame(0, $article->fresh()->views_count);
    }

    /** R9: a repeat view within the same session is de-duplicated. */
    public function test_news_view_deduplicated_within_session(): void
    {
        $article = $this->publishedArticle();

        $this->withSession(['news_viewed' => [$article->id]])
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'])
            ->get(route('news.show', $article->slug))->assertOk();

        $this->assertSame(0, $article->fresh()->views_count);
    }

    private function publishedArticle(): News
    {
        $cat = NewsCategory::create(['name' => 'Cat', 'slug' => 'cat']);

        return News::create([
            'category_id' => $cat->id, 'title_en' => 'Breaking', 'slug' => 'breaking',
            'content_en' => '<p>Body</p>', 'status' => 'published', 'published_at' => now(),
            'views_count' => 0, 'is_featured' => false,
        ]);
    }

    /**
     * H-02.1 regression: Key Metric labels must follow the READER'S locale, not
     * the locale that first warmed the (locale-agnostic) home cache.
     *
     * Before the fix, $stats was pre-resolved inside the cached payload, so the
     * first warming locale's labels bled into every subsequent locale.
     */
    public function test_key_metric_labels_follow_request_locale_not_cache_warmer(): void
    {
        $metric = KeyMetric::create([
            'value' => '15+',
            'label_en' => 'Years of Experience',
            'label_id' => 'Tahun Pengalaman',
            'status' => 'active',
            'display_order' => 1,
            'is_featured' => false,
        ]);

        // 1) Warm the cache via the default (en) locale.
        $this->get('/')->assertOk()->assertSee('Years of Experience');

        // 2+3) A later request in another locale hits the WARM cache but must
        //       still render the id label — not the en label baked at warm time.
        $this->get('/id')
            ->assertOk()
            ->assertSee('Tahun Pengalaman')
            ->assertDontSee('Years of Experience');

        // 4) A data change after the cache was built still resolves per active
        //    locale (the content observer busts the cache; stats rebuild per request).
        $metric->update(['label_id' => 'Tahun Berpengalaman']);

        $this->get('/id')->assertOk()->assertSee('Tahun Berpengalaman');
        $this->get('/')->assertOk()->assertSee('Years of Experience');
    }

    /**
     * R12 regression: when NO Key Metric exists, the homepage stat fallback labels
     * must be localized — English on `/`, Indonesian on `/id` — not hardcoded EN.
     */
    public function test_key_metric_fallback_labels_are_localized_when_empty(): void
    {
        // (1) No Key Metric exists (RefreshDatabase starts empty) → fallback path.
        $this->assertSame(0, KeyMetric::count());

        // (2) Default locale shows the English fallback.
        $this->get('/')->assertOk()->assertSee('Years of Experience');

        // (3+4) Indonesian locale shows the Indonesian fallback, NOT the English label.
        $this->get('/id')
            ->assertOk()
            ->assertSee('Tahun Pengalaman')
            ->assertDontSee('Years of Experience');
    }
}
