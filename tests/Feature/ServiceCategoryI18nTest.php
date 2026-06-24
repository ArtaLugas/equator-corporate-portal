<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Service Category i18n coverage, following the gold standard. `name` is the
 * required anchor; `description` is optional rich text (sanitized per locale);
 * meta_* are optional translatable SEO fields. The slug is generated from the
 * default-locale name and stays stable across locales.
 */
class ServiceCategoryI18nTest extends TestCase
{
    use RefreshDatabase;

    private function category(array $attributes = []): ServiceCategory
    {
        return ServiceCategory::create(array_merge([
            'name_en' => 'Topographic Mapping',
            'description_en' => '<p>We map terrain.</p>',
            'slug' => 'topographic-mapping',
            'display_order' => 1,
            'status' => 'active',
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Locale resolution & fallback
    |--------------------------------------------------------------------------
    */

    public function test_name_resolves_per_locale_with_fallback(): void
    {
        $category = $this->category([
            'name_en' => 'Survey',
            'name_id' => 'Survei',
        ]);

        app()->setLocale('en');
        $this->assertSame('Survey', $category->name);

        app()->setLocale('id');
        $this->assertSame('Survei', $category->name);
    }

    public function test_name_falls_back_to_english_when_translation_missing(): void
    {
        $category = $this->category([
            'name_en' => 'Survey',
            'name_id' => null,
        ]);

        app()->setLocale('id');
        $this->assertSame('Survey', $category->name); // fallback to default
    }

    public function test_translation_progress_reflects_completeness(): void
    {
        $category = $this->category(['name_id' => 'Pemetaan', 'description_id' => null]);

        $this->assertSame(50, $category->translationProgress('id'));

        $category->update(['description_id' => '<p>Kami memetakan medan.</p>']);
        $this->assertTrue($category->fresh()->isTranslated('id'));
    }

    /*
    |--------------------------------------------------------------------------
    | Admin form rendering
    |--------------------------------------------------------------------------
    */

    public function test_admin_create_form_renders_language_tabs(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.service-categories.create'))
            ->assertOk()
            ->assertSee('Category Name (EN)')
            ->assertSee('Category Name (ID)');
    }

    public function test_admin_edit_form_prefills_localized_values(): void
    {
        $category = $this->category(['name_en' => 'Survey', 'name_id' => 'Survei']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.service-categories.edit', $category))
            ->assertOk()
            ->assertSee('Survey')
            ->assertSee('Survei');
    }

    /*
    |--------------------------------------------------------------------------
    | Per-locale validation & slug
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_category_with_both_locales(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.service-categories.store'), [
                'name_en' => 'Drone Survey',
                'name_id' => 'Survei Drone',
                'description_en' => '<p>EN</p>',
                'description_id' => '<p>ID</p>',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.service-categories.index'));

        $this->assertDatabaseHas('service_categories', [
            'name_en' => 'Drone Survey',
            'name_id' => 'Survei Drone',
            'slug' => 'drone-survey', // slug derives from the default-locale name
        ]);
    }

    public function test_english_name_is_required(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.service-categories.store'), [
                'name_en' => '',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('name_en');
    }

    public function test_indonesian_is_optional_when_untouched(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.service-categories.store'), [
                'name_en' => 'English Only',
                'description_en' => '<p>EN</p>',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.service-categories.index'));
    }

    public function test_partial_indonesian_translation_is_rejected(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.service-categories.store'), [
                'name_en' => 'Mapping',
                'description_en' => '<p>EN</p>',
                'name_id' => 'Pemetaan',
                'description_id' => '', // <-- missing → must fail
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('description_id')
            ->assertSessionDoesntHaveErrors('name_id')
            ->assertSessionHasErrors('translation_id');
    }

    public function test_html_description_is_sanitized_per_locale(): void
    {
        $category = $this->category(['description_en' => '<p>Safe</p><script>alert(1)</script>']);

        $stored = $category->fresh()->description_en;
        $this->assertStringContainsString('Safe', $stored);
        $this->assertStringNotContainsString('<script>', $stored);
    }
}
