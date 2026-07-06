<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * News i18n coverage, mirroring the Service/Project gold standard: trait
 * resolution + fallback, per-locale validation (EN required, ID all-or-nothing),
 * translation progress, localized public rendering, config-controlled slug,
 * search, and HTML sanitization of the translatable rich-text field.
 */
class NewsI18nTest extends TestCase
{
    use RefreshDatabase;

    private function category(): NewsCategory
    {
        return NewsCategory::create(['name_en' => 'Cat', 'slug' => 'cat']);
    }

    private function published(array $attributes = []): News
    {
        return News::create(array_merge([
            'category_id' => $this->category()->id,
            'title_en' => 'Breaking News',
            'slug' => 'breaking-news',
            'status' => 'published',
            'published_at' => now(),
            'views_count' => 0,
            'is_featured' => false,
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Trait resolution & fallback
    |--------------------------------------------------------------------------
    */

    public function test_public_page_falls_back_to_english_when_translation_missing(): void
    {
        $this->published(['title_en' => 'Award Won', 'title_id' => null, 'content_en' => '<p>EN</p>']);

        $this->get('/news/breaking-news')->assertOk()->assertSee('Award Won');
        $this->get('/id/news/breaking-news')->assertOk()->assertSee('Award Won');
    }

    public function test_public_page_shows_indonesian_when_available(): void
    {
        $this->published(['title_en' => 'Award Won', 'title_id' => 'Penghargaan Diraih']);

        $this->get('/news/breaking-news')->assertOk()->assertSee('Award Won')->assertDontSee('Penghargaan Diraih');
        $this->get('/id/news/breaking-news')->assertOk()->assertSee('Penghargaan Diraih');
    }

    public function test_translation_progress_reflects_completeness(): void
    {
        $news = $this->published([
            'title_en' => 'A', 'content_en' => '<p>B</p>',
            'title_id' => 'A-id', 'content_id' => null,
        ]);

        $this->assertSame(50, $news->translationProgress('id'));

        $news->update(['content_id' => '<p>B-id</p>']);
        $this->assertTrue($news->fresh()->isTranslated('id'));
    }

    /*
    |--------------------------------------------------------------------------
    | Admin form rendering
    |--------------------------------------------------------------------------
    */

    public function test_admin_create_form_renders_language_tabs(): void
    {
        $this->category();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.news.create'))
            ->assertOk()
            ->assertSee('Title (EN)')
            ->assertSee('Title (ID)');
    }

    public function test_admin_edit_form_prefills_localized_values(): void
    {
        $news = $this->published(['title_en' => 'Award Won', 'title_id' => 'Penghargaan Diraih']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.news.edit', $news))
            ->assertOk()
            ->assertSee('Award Won')
            ->assertSee('Penghargaan Diraih');
    }

    /*
    |--------------------------------------------------------------------------
    | Per-locale validation
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_news_with_both_locales(): void
    {
        $cat = $this->category();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.news.store'), [
                'category_id' => $cat->id,
                'title_en' => 'Tech Update',
                'title_id' => 'Pembaruan Teknologi',
                'content_en' => '<p>EN</p>',
                'content_id' => '<p>ID</p>',
                'status' => 'published',
            ])
            ->assertRedirect(route('admin.news.index'));

        $this->assertDatabaseHas('news', [
            'title_en' => 'Tech Update',
            'title_id' => 'Pembaruan Teknologi',
            'slug' => 'tech-update',
        ]);
    }

    public function test_english_title_is_required(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.news.store'), [
                'category_id' => $this->category()->id,
                'title_en' => '',
                'status' => 'draft',
            ])
            ->assertSessionHasErrors('title_en');
    }

    public function test_indonesian_is_optional_when_untouched(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.news.store'), [
                'category_id' => $this->category()->id,
                'title_en' => 'English Only',
                'content_en' => '<p>EN</p>',
                'status' => 'draft',
            ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.news.index'));
    }

    public function test_partial_indonesian_translation_is_rejected(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.news.store'), [
                'category_id' => $this->category()->id,
                'title_en' => 'Headline',
                'content_en' => '<p>EN</p>',
                'title_id' => 'Judul',
                'content_id' => '', // <-- missing → must fail
                'status' => 'draft',
            ])
            ->assertSessionHasErrors('content_id')
            ->assertSessionDoesntHaveErrors('title_id')
            ->assertSessionHasErrors('translation_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Slug behaviour & sanitization
    |--------------------------------------------------------------------------
    */

    public function test_slug_regenerates_when_title_en_changes_and_enabled(): void
    {
        config(['cms.auto_regenerate_slug' => true]);
        $news = $this->published(['title_en' => 'Old Title', 'slug' => 'old-title']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.news.update', $news), [
                'category_id' => $news->category_id,
                'title_en' => 'New Title',
                'status' => 'published',
            ])
            ->assertRedirect();

        $this->assertSame('new-title', $news->fresh()->slug);
    }

    public function test_slug_is_frozen_when_auto_regenerate_disabled(): void
    {
        config(['cms.auto_regenerate_slug' => false]);
        $news = $this->published(['title_en' => 'Old Title', 'slug' => 'old-title']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.news.update', $news), [
                'category_id' => $news->category_id,
                'title_en' => 'Completely Different',
                'status' => 'published',
            ])
            ->assertRedirect();

        $this->assertSame('old-title', $news->fresh()->slug);
    }

    public function test_translatable_html_content_is_sanitized_per_locale(): void
    {
        $news = $this->published([
            'content_en' => '<p>Safe</p><script>alert(1)</script>',
        ]);

        $stored = $news->fresh()->content_en;
        $this->assertStringContainsString('Safe', $stored);
        $this->assertStringNotContainsString('<script>', $stored);
    }

    /*
    |--------------------------------------------------------------------------
    | Search across locales / facets
    |--------------------------------------------------------------------------
    */

    public function test_search_matches_indonesian_term_and_facets(): void
    {
        $cat = NewsCategory::create(['name_en' => 'Geospatial', 'slug' => 'geospatial']);
        $this->published([
            'category_id' => $cat->id,
            'title_en' => 'Consulting', 'title_id' => 'Konsultasi', 'slug' => 'unique-marker',
        ]);

        $admin = Admin::factory()->create();

        foreach (['Konsultasi', 'unique-marker', 'published', 'Geospatial'] as $term) {
            $this->actingAs($admin, 'admin')
                ->get(route('admin.news.index', ['search' => $term]))
                ->assertOk()
                ->assertSee('Consulting');
        }
    }
}
