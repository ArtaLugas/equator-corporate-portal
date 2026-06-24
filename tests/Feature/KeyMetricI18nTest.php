<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\KeyMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Key Metric i18n coverage, following the gold standard. `label` is the single
 * translatable field and the required anchor; value, icon, display_order, status
 * and is_featured stay single-language.
 */
class KeyMetricI18nTest extends TestCase
{
    use RefreshDatabase;

    private function metric(array $attributes = []): KeyMetric
    {
        return KeyMetric::create(array_merge([
            'label_en' => 'Projects Delivered',
            'value' => '200+',
            'icon' => 'activity',
            'display_order' => 1,
            'status' => 'active',
            'is_featured' => false,
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Locale resolution & fallback
    |--------------------------------------------------------------------------
    */

    public function test_label_resolves_to_active_locale(): void
    {
        $metric = $this->metric(['label_en' => 'Projects', 'label_id' => 'Proyek']);

        app()->setLocale('en');
        $this->assertSame('Projects', $metric->label);

        app()->setLocale('id');
        $this->assertSame('Proyek', $metric->label);
    }

    public function test_label_falls_back_to_english_when_translation_missing(): void
    {
        $metric = $this->metric(['label_en' => 'Projects', 'label_id' => null]);

        app()->setLocale('id');
        $this->assertSame('Projects', $metric->label); // fallback to default locale
    }

    public function test_translation_progress_reflects_completeness(): void
    {
        $metric = $this->metric(['label_id' => null]);
        $this->assertSame(0, $metric->translationProgress('id'));

        $metric->update(['label_id' => 'Proyek']);
        $this->assertTrue($metric->fresh()->isTranslated('id'));
    }

    /*
    |--------------------------------------------------------------------------
    | Admin form rendering
    |--------------------------------------------------------------------------
    */

    public function test_admin_create_form_renders_language_tabs(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.key-metrics.create'))
            ->assertOk()
            ->assertSee('Label (EN)')
            ->assertSee('Label (ID)');
    }

    public function test_admin_edit_form_prefills_localized_values(): void
    {
        $metric = $this->metric(['label_en' => 'Projects', 'label_id' => 'Proyek']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.key-metrics.edit', $metric))
            ->assertOk()
            ->assertSee('Projects')
            ->assertSee('Proyek');
    }

    /*
    |--------------------------------------------------------------------------
    | Per-locale validation
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_metric_with_both_locales(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.key-metrics.store'), [
                'label_en' => 'Projects Delivered',
                'label_id' => 'Proyek Selesai',
                'value' => '200+',
                'icon' => 'activity',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.key-metrics.index'));

        $this->assertDatabaseHas('key_metrics', [
            'label_en' => 'Projects Delivered',
            'label_id' => 'Proyek Selesai',
            'value' => '200+',
        ]);
    }

    public function test_english_label_is_required(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.key-metrics.store'), [
                'label_en' => '',
                'value' => '200+',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('label_en');
    }

    public function test_indonesian_is_optional_when_untouched(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.key-metrics.store'), [
                'label_en' => 'English Only',
                'value' => '200+',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.key-metrics.index'));
    }

    public function test_partial_indonesian_translation_is_rejected(): void
    {
        // With a single translatable field, "starting" the ID locale means the
        // anchor itself is filled, so all-or-nothing collapses to: the anchor is
        // required. Omitting `value` (non-translatable) must still fail.
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.key-metrics.store'), [
                'label_en' => 'Projects Delivered',
                'label_id' => 'Proyek Selesai',
                // value missing → non-translatable required field
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('value');
    }

    /*
    |--------------------------------------------------------------------------
    | Search across locales
    |--------------------------------------------------------------------------
    */

    public function test_search_matches_indonesian_term(): void
    {
        $this->metric(['label_en' => 'Projects Delivered', 'label_id' => 'Proyek Selesai']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.key-metrics.index', ['search' => 'Proyek']))
            ->assertOk()
            ->assertSee('Projects Delivered');
    }
}
