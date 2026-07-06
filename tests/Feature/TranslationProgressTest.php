<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\TranslationProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the translation-progress monitoring tools: the shared roll-up, the
 * `i18n:status` command, and the admin overview page. Read-only — never changes
 * data or fallback behaviour.
 */
class TranslationProgressTest extends TestCase
{
    use RefreshDatabase;

    private function category(): ServiceCategory
    {
        return ServiceCategory::create(['name_en' => 'Cat', 'slug' => 'cat', 'status' => 'active', 'display_order' => 1]);
    }

    public function test_rollup_counts_complete_partial_and_untranslated(): void
    {
        $cat = $this->category();

        // Complete: only EN name filled, and it is translated → 100%.
        Service::create(['category_id' => $cat->id, 'name_en' => 'A', 'name_id' => 'A-id', 'slug' => 'a', 'status' => 'published', 'is_featured' => false]);
        // Partial: EN has name + description; only name is translated → 50%.
        Service::create(['category_id' => $cat->id, 'name_en' => 'B', 'description_en' => '<p>x</p>', 'name_id' => 'B-id', 'slug' => 'b', 'status' => 'published', 'is_featured' => false]);
        // Untranslated: EN name filled, no ID → 0%.
        Service::create(['category_id' => $cat->id, 'name_en' => 'C', 'slug' => 'c', 'status' => 'published', 'is_featured' => false]);

        $rows = TranslationProgress::forLocale('id');
        $services = collect($rows)->firstWhere('label', 'Services');

        $this->assertSame(3, $services['total']);
        $this->assertSame(1, $services['complete']);
        $this->assertSame(1, $services['partial']);
        $this->assertSame(1, $services['untranslated']);

        // Every registry module appears in the report (14 original + Company
        // Credentials + Credential Items + News Categories).
        $this->assertCount(17, $rows);
    }

    public function test_command_runs_successfully(): void
    {
        $this->artisan('i18n:status')->assertSuccessful();
        $this->artisan('i18n:status', ['--locale' => 'id'])->assertSuccessful();
        $this->artisan('i18n:status', ['--locale' => 'xx'])->assertFailed();
    }

    public function test_admin_progress_page_renders(): void
    {
        $this->category();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.translations.index'))
            ->assertOk()
            ->assertSee('Translation Progress')
            ->assertSee('Services')
            ->assertSee('Teams');
    }
}
