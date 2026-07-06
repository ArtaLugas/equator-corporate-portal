<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * News uses SoftDeletes + a restrictOnDelete FK on news.category_id, so the
 * delete guard must count trashed articles too (withTrashed) — otherwise a
 * category whose articles are all in Trash slips past the guard and fails with
 * a raw FK violation. Mirrors ServiceCategory's correct guard.
 */
class NewsCategoryDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_with_only_trashed_articles_is_blocked_by_the_guard(): void
    {
        $cat = NewsCategory::create(['name_en' => 'Cat', 'slug' => 'cat']);
        $news = News::create([
            'category_id' => $cat->id, 'title_en' => 'X', 'slug' => 'x',
            'status' => 'draft', 'views_count' => 0, 'is_featured' => false,
        ]);
        $news->delete(); // soft-delete → row stays, FK still references the category

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->delete(route('admin.news-categories.destroy', $cat))
            ->assertRedirect()
            // The guard (not the DB FK) caught it → the actionable "Trash" message,
            // NOT the generic "Failed to delete" from a caught query exception.
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Trash'));

        $this->assertDatabaseHas('news_categories', ['id' => $cat->id]);
    }

    public function test_empty_category_deletes_successfully(): void
    {
        $cat = NewsCategory::create(['name_en' => 'Empty', 'slug' => 'empty']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->delete(route('admin.news-categories.destroy', $cat))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('news_categories', ['id' => $cat->id]);
    }
}
