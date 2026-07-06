<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\NewsCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * News Category i18n coverage, following the gold standard. `name` is the only
 * translatable field (and the required anchor); the slug is generated from the
 * default-locale name and stays stable across locales.
 */
class NewsCategoryI18nTest extends TestCase
{
    use RefreshDatabase;

    private function category(array $attributes = []): NewsCategory
    {
        return NewsCategory::create(array_merge([
            'name_en' => 'Company Updates',
            'slug' => 'company-updates',
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
            'name_en' => 'Company Updates',
            'name_id' => 'Kabar Perusahaan',
        ]);

        app()->setLocale('en');
        $this->assertSame('Company Updates', $category->name);

        app()->setLocale('id');
        $this->assertSame('Kabar Perusahaan', $category->name);
    }

    public function test_name_falls_back_to_english_when_translation_missing(): void
    {
        $category = $this->category([
            'name_en' => 'Company Updates',
            'name_id' => null,
        ]);

        app()->setLocale('id');
        $this->assertSame('Company Updates', $category->name); // fallback to default
    }

    public function test_translation_progress_reflects_completeness(): void
    {
        $untranslated = $this->category(['name_id' => null]);
        $this->assertSame(0, $untranslated->translationProgress('id'));

        $translated = $this->category([
            'name_en' => 'Press', 'name_id' => 'Pers', 'slug' => 'press',
        ]);
        $this->assertTrue($translated->isTranslated('id'));
    }

    /*
    |--------------------------------------------------------------------------
    | Admin form rendering
    |--------------------------------------------------------------------------
    */

    public function test_admin_create_form_renders_language_tabs(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.news-categories.create'))
            ->assertOk()
            ->assertSee('Category Name (EN)')
            ->assertSee('Category Name (ID)');
    }

    public function test_admin_edit_form_prefills_localized_values(): void
    {
        $category = $this->category(['name_en' => 'Updates', 'name_id' => 'Kabar', 'slug' => 'updates']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.news-categories.edit', $category))
            ->assertOk()
            ->assertSee('Updates')
            ->assertSee('Kabar');
    }

    /*
    |--------------------------------------------------------------------------
    | Per-locale validation & slug
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_category_with_both_locales(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.news-categories.store'), [
                'name_en' => 'Press Release',
                'name_id' => 'Rilis Pers',
            ])
            ->assertRedirect(route('admin.news-categories.index'));

        $this->assertDatabaseHas('news_categories', [
            'name_en' => 'Press Release',
            'name_id' => 'Rilis Pers',
            'slug' => 'press-release', // slug derives from the default-locale name
        ]);
    }

    public function test_english_name_is_required(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.news-categories.store'), [
                'name_en' => '',
            ])
            ->assertSessionHasErrors('name_en');
    }

    public function test_indonesian_is_optional_when_untouched(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.news-categories.store'), [
                'name_en' => 'English Only',
            ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.news-categories.index'));

        $this->assertDatabaseHas('news_categories', [
            'name_en' => 'English Only',
            'name_id' => null,
        ]);
    }

    public function test_slug_stays_stable_when_only_translation_changes(): void
    {
        $category = $this->category(['name_en' => 'Awards', 'slug' => 'awards']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.news-categories.update', $category), [
                'name_en' => 'Awards',
                'name_id' => 'Penghargaan',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('news_categories', [
            'id' => $category->id,
            'name_id' => 'Penghargaan',
            'slug' => 'awards', // unchanged — default-locale name did not change
        ]);
    }
}
