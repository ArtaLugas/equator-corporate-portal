<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CoreValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Core Value i18n coverage, following the gold standard. `title` is the required
 * anchor; `description` is optional rich text (sanitized per locale). Core values
 * render on the (cached) About page.
 */
class CoreValueI18nTest extends TestCase
{
    use RefreshDatabase;

    private function value(array $attributes = []): CoreValue
    {
        return CoreValue::create(array_merge([
            'title_en' => 'Integrity',
            'description_en' => '<p>We act with integrity.</p>',
            'icon' => 'shield',
            'display_order' => 1,
            'status' => 'active',
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Public rendering & fallback (About page)
    |--------------------------------------------------------------------------
    */

    public function test_about_falls_back_to_english_when_translation_missing(): void
    {
        $this->value(['title_en' => 'Excellence', 'title_id' => null]);
        Cache::flush();

        $this->get('/about')->assertOk()->assertSee('Excellence');
        $this->get('/id/about')->assertOk()->assertSee('Excellence'); // fallback
    }

    public function test_about_shows_indonesian_when_available(): void
    {
        $this->value(['title_en' => 'Excellence', 'title_id' => 'Keunggulan']);
        Cache::flush();

        $this->get('/about')->assertOk()->assertSee('Excellence')->assertDontSee('Keunggulan');
        $this->get('/id/about')->assertOk()->assertSee('Keunggulan');
    }

    public function test_translation_progress_reflects_completeness(): void
    {
        $value = $this->value(['title_id' => 'Integritas', 'description_id' => null]);

        $this->assertSame(50, $value->translationProgress('id'));

        $value->update(['description_id' => '<p>Kami bertindak dengan integritas.</p>']);
        $this->assertTrue($value->fresh()->isTranslated('id'));
    }

    /*
    |--------------------------------------------------------------------------
    | Admin form rendering
    |--------------------------------------------------------------------------
    */

    public function test_admin_create_form_renders_language_tabs(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.core-values.create'))
            ->assertOk()
            ->assertSee('Title (EN)')
            ->assertSee('Title (ID)');
    }

    public function test_admin_edit_form_prefills_localized_values(): void
    {
        $value = $this->value(['title_en' => 'Excellence', 'title_id' => 'Keunggulan']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.core-values.edit', $value))
            ->assertOk()
            ->assertSee('Excellence')
            ->assertSee('Keunggulan');
    }

    /*
    |--------------------------------------------------------------------------
    | Per-locale validation
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_value_with_both_locales(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.core-values.store'), [
                'title_en' => 'Sustainability',
                'title_id' => 'Keberlanjutan',
                'description_en' => '<p>EN</p>',
                'description_id' => '<p>ID</p>',
                'icon' => 'leaf',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.core-values.index'));

        $this->assertDatabaseHas('core_values', [
            'title_en' => 'Sustainability',
            'title_id' => 'Keberlanjutan',
        ]);
    }

    public function test_english_title_is_required(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.core-values.store'), [
                'title_en' => '',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('title_en');
    }

    public function test_indonesian_is_optional_when_untouched(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.core-values.store'), [
                'title_en' => 'English Only',
                'description_en' => '<p>EN</p>',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.core-values.index'));
    }

    public function test_partial_indonesian_translation_is_rejected(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.core-values.store'), [
                'title_en' => 'Integrity',
                'description_en' => '<p>EN</p>',
                'title_id' => 'Integritas',
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
        $value = $this->value(['description_en' => '<p>Safe</p><script>alert(1)</script>']);

        $stored = $value->fresh()->description_en;
        $this->assertStringContainsString('Safe', $stored);
        $this->assertStringNotContainsString('<script>', $stored);
    }

    /*
    |--------------------------------------------------------------------------
    | Search across locales
    |--------------------------------------------------------------------------
    */

    public function test_search_matches_indonesian_term(): void
    {
        $this->value(['title_en' => 'Integrity', 'title_id' => 'Integritas']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.core-values.index', ['search' => 'Integritas']))
            ->assertOk()
            ->assertSee('Integrity');
    }
}
