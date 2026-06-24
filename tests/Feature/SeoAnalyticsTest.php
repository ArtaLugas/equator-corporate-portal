<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * SEO & GA4: consent-gated analytics, Search Console verification, sitemap
 * hreflang, robots, and NewsArticle structured data. All IDs come from the CMS.
 */
class SeoAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush(); // settings + home + sitemap are cached
    }

    public function test_ga4_is_absent_until_configured(): void
    {
        $this->get('/')->assertOk()->assertDontSee('googletagmanager.com');
    }

    public function test_ga4_is_consent_gated_and_id_comes_from_cms(): void
    {
        Setting::current()->update(['ga4_measurement_id' => 'G-TEST12345']);

        $res = $this->get('/')->assertOk();

        $res->assertSee('G-TEST12345');               // id from CMS, not hardcoded
        $res->assertSee('googletagmanager.com');      // gtag.js
        $res->assertSee('analyticsGranted');          // consent gate function
        $res->assertSee('cookie-consent-updated');    // loads on consent grant
        $res->assertSee('contact_form_submit');       // event wiring present
        $res->assertSee('external_link_click');
    }

    public function test_search_console_verification_from_cms(): void
    {
        $this->get('/')->assertOk()->assertDontSee('google-site-verification');

        Setting::current()->update(['gsc_verification' => 'verify-token-abc']);

        $this->get('/')
            ->assertOk()
            ->assertSee('google-site-verification')
            ->assertSee('verify-token-abc');
    }

    public function test_sitemap_has_hreflang_legal_and_detail_pages(): void
    {
        // A published detail page must appear in BOTH locales (guards the
        // by-reference bug where detail entries were silently dropped).
        $cat = ServiceCategory::create(['name_en' => 'Cat', 'slug' => 'cat', 'status' => 'active', 'display_order' => 1]);
        Service::create(['category_id' => $cat->id, 'name_en' => 'Topo Survey', 'slug' => 'topo-survey', 'status' => 'published', 'is_featured' => false]);

        $res = $this->get('/sitemap.xml')->assertOk();
        $res->assertHeader('Content-Type', 'application/xml');

        $body = $res->getContent();
        $this->assertStringContainsString('xmlns:xhtml="http://www.w3.org/1999/xhtml"', $body);
        $this->assertStringContainsString('hreflang="id"', $body);
        $this->assertStringContainsString('hreflang="x-default"', $body);
        $this->assertStringContainsString('/privacy', $body);
        $this->assertStringContainsString('/cookies', $body);
        $this->assertStringContainsString('/id/about', $body);
        $this->assertStringContainsString('/services/topo-survey', $body);
        $this->assertStringContainsString('/id/services/topo-survey', $body);
    }

    /**
     * R16 regression: the sitemap must follow the same public-status gate as the
     * project detail page (scopePublic → excludes `planned`). A `planned` project's
     * detail page 404s, so advertising its URL would list a dead link and leak a
     * non-public slug; a `completed` project must still appear.
     */
    public function test_sitemap_excludes_non_public_projects(): void
    {
        Project::create(['name_en' => 'Public Job', 'slug' => 'public-job', 'status' => 'completed', 'is_featured' => false]);
        Project::create(['name_en' => 'Secret Job', 'slug' => 'secret-job', 'status' => 'planned', 'is_featured' => false]);

        $body = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString('/projects/public-job', $body);
        $this->assertStringNotContainsString('/projects/secret-job', $body);
    }

    public function test_robots_txt(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap:');
    }

    public function test_news_article_has_structured_data(): void
    {
        $cat = NewsCategory::create(['name' => 'Updates', 'slug' => 'updates']);
        News::create([
            'category_id' => $cat->id,
            'title_en' => 'Equator Wins Award',
            'slug' => 'equator-wins-award',
            'content_en' => '<p>Body text.</p>',
            'status' => 'published',
            'published_at' => now(),
            'views_count' => 0,
            'is_featured' => false,
        ]);

        $this->get('/news/equator-wins-award')
            ->assertOk()
            ->assertSee('application/ld+json', false)
            ->assertSee('NewsArticle')
            ->assertSee('BreadcrumbList');
    }
}
