<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Scheduled publishing: a "published" article whose published_at is in the future
 * must be embargoed from every public surface (index, article page, sitemap) until
 * its time arrives. A null published_at stays immediately visible (legacy-safe).
 */
class NewsScheduledPublishTest extends TestCase
{
    use RefreshDatabase;

    private function article(string $title, string $slug, ?string $publishedAt, string $status = 'published'): News
    {
        $category = NewsCategory::firstOrCreate(['slug' => 'general'], ['name_en' => 'General']);

        return News::create([
            'category_id' => $category->id,
            'title_en' => $title,
            'content_en' => '<p>'.$title.' body.</p>',
            'slug' => $slug,
            'status' => $status,
            'published_at' => $publishedAt,
            'views_count' => 0,
            'is_featured' => false,
        ]);
    }

    public function test_future_dated_article_is_hidden_from_the_index(): void
    {
        $this->article('Visible Announcement', 'visible-announcement', now()->subDay()->toDateTimeString());
        $this->article('Embargoed Scoop', 'embargoed-scoop', now()->addWeek()->toDateTimeString());

        $this->get(route('news.index'))
            ->assertOk()
            ->assertSee('Visible Announcement')
            ->assertDontSee('Embargoed Scoop');
    }

    public function test_future_dated_article_is_not_reachable_by_url(): void
    {
        $this->article('Embargoed Scoop', 'embargoed-scoop', now()->addWeek()->toDateTimeString());

        $this->get(route('news.show', 'embargoed-scoop'))->assertNotFound();
    }

    public function test_past_dated_article_is_visible_on_its_page(): void
    {
        $this->article('Visible Announcement', 'visible-announcement', now()->subDay()->toDateTimeString());

        $this->get(route('news.show', 'visible-announcement'))
            ->assertOk()
            ->assertSee('Visible Announcement');
    }

    public function test_sitemap_excludes_future_dated_articles(): void
    {
        $this->article('Visible Announcement', 'visible-announcement', now()->subDay()->toDateTimeString());
        $this->article('Embargoed Scoop', 'embargoed-scoop', now()->addWeek()->toDateTimeString());

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('visible-announcement')
            ->assertDontSee('embargoed-scoop');
    }

    public function test_null_published_at_stays_visible(): void
    {
        $this->article('Legacy Post', 'legacy-post', null);

        $this->get(route('news.show', 'legacy-post'))->assertOk();
    }
}
