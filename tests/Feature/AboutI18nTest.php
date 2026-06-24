<?php

namespace Tests\Feature;

use App\Models\AboutContent;
use App\Models\AboutSection;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * About module i18n coverage (sections + contents), following the gold standard.
 * Only user-facing fields are multilingual: AboutSection.name, AboutContent.title
 * and AboutContent.content. The internal `key` (derived from the default-locale
 * title) and `slug` stay single-language.
 */
class AboutI18nTest extends TestCase
{
    use RefreshDatabase;

    private function section(array $attributes = []): AboutSection
    {
        return AboutSection::create(array_merge([
            'name_en' => 'Our Story',
            'slug' => 'our-story',
            'display_order' => 1,
            'status' => 'active',
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Public rendering & fallback
    |--------------------------------------------------------------------------
    */

    public function test_about_page_falls_back_to_english_when_translation_missing(): void
    {
        $this->section(['name_en' => 'Heritage', 'name_id' => null]);
        Cache::flush();

        $this->get('/about')->assertOk()->assertSee('Heritage');
        $this->get('/id/about')->assertOk()->assertSee('Heritage'); // fallback
    }

    public function test_about_page_shows_indonesian_when_available(): void
    {
        $this->section(['name_en' => 'Heritage', 'name_id' => 'Warisan']);
        Cache::flush();

        $this->get('/about')->assertOk()->assertSee('Heritage')->assertDontSee('Warisan');
        $this->get('/id/about')->assertOk()->assertSee('Warisan');
    }

    public function test_translation_progress_for_section_and_content(): void
    {
        $section = $this->section(['name_en' => 'A', 'name_id' => 'A-id']);
        $this->assertTrue($section->isTranslated('id'));

        $content = AboutContent::create([
            'section_id' => $section->id, 'key' => 'vision', 'display_order' => 1, 'status' => 'active',
            'title_en' => 'Vision', 'content_en' => '<p>EN</p>', 'title_id' => 'Visi', 'content_id' => null,
        ]);
        $this->assertSame(50, $content->translationProgress('id'));
    }

    /*
    |--------------------------------------------------------------------------
    | About Section — admin
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_section_with_both_locales(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.about-sections.store'), [
                'name_en' => 'Company Profile',
                'name_id' => 'Profil Perusahaan',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.about-sections.index'));

        $this->assertDatabaseHas('about_sections', [
            'name_en' => 'Company Profile',
            'name_id' => 'Profil Perusahaan',
            'slug' => 'company-profile', // from name_en
        ]);
    }

    public function test_section_english_name_is_required(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.about-sections.store'), [
                'name_en' => '',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('name_en');
    }

    public function test_section_create_form_renders_language_tabs(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.about-sections.create'))
            ->assertOk()
            ->assertSee('Section Name (EN)')
            ->assertSee('Section Name (ID)');
    }

    /*
    |--------------------------------------------------------------------------
    | About Content — admin
    |--------------------------------------------------------------------------
    */

    public function test_content_key_is_derived_from_default_locale_title(): void
    {
        $section = $this->section();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.about-contents.store'), [
                'section_id' => $section->id,
                'title_en' => 'Vision Statement',
                'title_id' => 'Pernyataan Visi',
                'content_en' => '<p>EN</p>',
                'content_id' => '<p>ID</p>',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.about-contents.index'));

        // Key comes from the EN title, NOT the ID title.
        $this->assertDatabaseHas('about_contents', [
            'title_en' => 'Vision Statement',
            'title_id' => 'Pernyataan Visi',
            'key' => 'vision_statement',
        ]);
    }

    public function test_content_title_is_optional(): void
    {
        $section = $this->section();

        // A "lead" block may have content but no title — must be allowed.
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.about-contents.store'), [
                'section_id' => $section->id,
                'content_en' => '<p>Lead narrative</p>',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.about-contents.index'));
    }

    public function test_content_partial_indonesian_translation_is_rejected(): void
    {
        $section = $this->section();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.about-contents.store'), [
                'section_id' => $section->id,
                'title_en' => 'Vision',
                'content_en' => '<p>EN</p>',
                'title_id' => 'Visi',
                'content_id' => '', // <-- missing → must fail
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('content_id')
            ->assertSessionDoesntHaveErrors('title_id')
            ->assertSessionHasErrors('translation_id');
    }

    public function test_content_html_is_sanitized_per_locale(): void
    {
        $section = $this->section();

        $content = AboutContent::create([
            'section_id' => $section->id, 'key' => 'lead', 'display_order' => 1, 'status' => 'active',
            'content_en' => '<p>Safe</p><script>alert(1)</script>',
        ]);

        $stored = $content->fresh()->content_en;
        $this->assertStringContainsString('Safe', $stored);
        $this->assertStringNotContainsString('<script>', $stored);
    }

    public function test_content_create_form_renders_language_tabs(): void
    {
        $this->section();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.about-contents.create'))
            ->assertOk()
            ->assertSee('Title (EN)')
            ->assertSee('Title (ID)');
    }
}
