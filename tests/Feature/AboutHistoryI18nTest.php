<?php

namespace Tests\Feature;

use App\Models\AboutHistory;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * About History i18n coverage, following the gold standard. `title` is the
 * required anchor; `description` is optional rich text (sanitized per locale).
 */
class AboutHistoryI18nTest extends TestCase
{
    use RefreshDatabase;

    private function history(array $attributes = []): AboutHistory
    {
        return AboutHistory::create(array_merge([
            'year' => 2010,
            'title_en' => 'Company Founded',
            'description_en' => '<p>We started the company.</p>',
            'display_order' => 1,
            'status' => 'active',
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Resolution & fallback
    |--------------------------------------------------------------------------
    */

    public function test_resolves_default_locale_and_falls_back_to_english(): void
    {
        $history = $this->history(['title_en' => 'Founded', 'title_id' => null]);

        app()->setLocale('en');
        $this->assertSame('Founded', $history->title);

        app()->setLocale('id');
        $this->assertSame('Founded', $history->title); // fallback to EN
    }

    public function test_resolves_indonesian_when_available(): void
    {
        $history = $this->history(['title_en' => 'Founded', 'title_id' => 'Didirikan']);

        app()->setLocale('id');
        $this->assertSame('Didirikan', $history->title);

        app()->setLocale('en');
        $this->assertSame('Founded', $history->title);
    }

    public function test_translation_progress_reflects_completeness(): void
    {
        $history = $this->history(['title_id' => 'Didirikan', 'description_id' => null]);

        $this->assertSame(50, $history->translationProgress('id'));

        $history->update(['description_id' => '<p>Kami memulai perusahaan.</p>']);
        $this->assertTrue($history->fresh()->isTranslated('id'));
    }

    /*
    |--------------------------------------------------------------------------
    | Admin form rendering
    |--------------------------------------------------------------------------
    */

    public function test_admin_create_form_renders_language_tabs(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.about-histories.create'))
            ->assertOk()
            ->assertSee('Title (EN)')
            ->assertSee('Title (ID)');
    }

    public function test_admin_edit_form_prefills_localized_values(): void
    {
        $history = $this->history(['title_en' => 'Founded', 'title_id' => 'Didirikan']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.about-histories.edit', $history))
            ->assertOk()
            ->assertSee('Founded')
            ->assertSee('Didirikan');
    }

    /*
    |--------------------------------------------------------------------------
    | Per-locale validation
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_history_with_both_locales(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.about-histories.store'), [
                'year' => 2015,
                'title_en' => 'Expansion',
                'title_id' => 'Ekspansi',
                'description_en' => '<p>EN</p>',
                'description_id' => '<p>ID</p>',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.about-histories.index'));

        $this->assertDatabaseHas('about_histories', [
            'title_en' => 'Expansion',
            'title_id' => 'Ekspansi',
        ]);
    }

    public function test_english_title_is_required(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.about-histories.store'), [
                'year' => 2015,
                'title_en' => '',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('title_en');
    }

    public function test_indonesian_is_optional_when_untouched(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.about-histories.store'), [
                'year' => 2015,
                'title_en' => 'English Only',
                'description_en' => '<p>EN</p>',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.about-histories.index'));
    }

    public function test_partial_indonesian_translation_is_rejected(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.about-histories.store'), [
                'year' => 2015,
                'title_en' => 'Founded',
                'description_en' => '<p>EN</p>',
                'title_id' => 'Didirikan',
                'description_id' => '', // <-- missing → must fail
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('description_id')
            ->assertSessionDoesntHaveErrors('title_id')
            ->assertSessionHasErrors('translation_id');
    }

    public function test_html_description_is_sanitized_per_locale(): void
    {
        $history = $this->history(['description_en' => '<p>Safe</p><script>alert(1)</script>']);

        $stored = $history->fresh()->description_en;
        $this->assertStringContainsString('Safe', $stored);
        $this->assertStringNotContainsString('<script>', $stored);
    }
}
