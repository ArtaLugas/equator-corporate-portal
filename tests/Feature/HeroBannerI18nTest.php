<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\HeroBanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Hero Banner i18n coverage, following the gold standard. `title` is the required
 * anchor; `subtitle` and `button_text` are optional plain-text fields. None are
 * HTML; there is no slug.
 */
class HeroBannerI18nTest extends TestCase
{
    use RefreshDatabase;

    private function banner(array $attributes = []): HeroBanner
    {
        return HeroBanner::create(array_merge([
            'title_en' => 'Empowering Development',
            'subtitle_en' => 'We build the future.',
            'button_text_en' => 'Learn More',
            'button_link' => 'https://example.com',
            'display_order' => 1,
            'status' => 'active',
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Per-locale resolution & fallback
    |--------------------------------------------------------------------------
    */

    public function test_title_falls_back_to_english_when_translation_missing(): void
    {
        $banner = $this->banner(['title_en' => 'Excellence', 'title_id' => null]);

        $this->assertSame('Excellence', $banner->translate('title', 'en'));
        $this->assertSame('Excellence', $banner->translate('title', 'id')); // fallback
    }

    public function test_title_resolves_indonesian_when_available(): void
    {
        $banner = $this->banner(['title_en' => 'Excellence', 'title_id' => 'Keunggulan']);

        $this->assertSame('Excellence', $banner->translate('title', 'en'));
        $this->assertSame('Keunggulan', $banner->translate('title', 'id'));

        app()->setLocale('id');
        $this->assertSame('Keunggulan', $banner->title);

        app()->setLocale('en');
        $this->assertSame('Excellence', $banner->title);
    }

    public function test_translation_progress_reflects_completeness(): void
    {
        $banner = $this->banner([
            'title_id' => 'Memberdayakan Pembangunan',
            'subtitle_id' => null,
            'button_text_id' => null,
        ]);

        // 1 of 3 source fields translated.
        $this->assertSame(33, $banner->translationProgress('id'));

        $banner->update([
            'subtitle_id' => 'Kami membangun masa depan.',
            'button_text_id' => 'Selengkapnya',
        ]);

        $this->assertTrue($banner->fresh()->isTranslated('id'));
    }

    /*
    |--------------------------------------------------------------------------
    | Admin form rendering
    |--------------------------------------------------------------------------
    */

    public function test_admin_create_form_renders_language_tabs(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.hero-banners.create'))
            ->assertOk()
            ->assertSee('Banner Title (EN)')
            ->assertSee('Banner Title (ID)');
    }

    public function test_admin_edit_form_prefills_localized_values(): void
    {
        $banner = $this->banner(['title_en' => 'Excellence', 'title_id' => 'Keunggulan']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.hero-banners.edit', $banner))
            ->assertOk()
            ->assertSee('Excellence')
            ->assertSee('Keunggulan');
    }

    /*
    |--------------------------------------------------------------------------
    | Per-locale validation
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_banner_with_both_locales(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.hero-banners.store'), [
                'title_en' => 'Sustainability',
                'title_id' => 'Keberlanjutan',
                'subtitle_en' => 'Building tomorrow.',
                'subtitle_id' => 'Membangun esok.',
                'button_text_en' => 'Discover',
                'button_text_id' => 'Telusuri',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.hero-banners.index'));

        $this->assertDatabaseHas('hero_banners', [
            'title_en' => 'Sustainability',
            'title_id' => 'Keberlanjutan',
        ]);
    }

    public function test_english_title_is_required(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.hero-banners.store'), [
                'title_en' => '',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('title_en');
    }

    public function test_indonesian_is_optional_when_untouched(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.hero-banners.store'), [
                'title_en' => 'English Only',
                'subtitle_en' => 'EN subtitle',
                'button_text_en' => 'Go',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.hero-banners.index'));
    }

    public function test_partial_indonesian_translation_is_rejected(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.hero-banners.store'), [
                'title_en' => 'Integrity',
                'subtitle_en' => 'EN subtitle',
                'button_text_en' => 'Go',
                'title_id' => 'Integritas',
                'subtitle_id' => '', // <-- missing → must fail
                'button_text_id' => '', // <-- missing → must fail
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('subtitle_id')
            ->assertSessionHasErrors('button_text_id')
            ->assertSessionDoesntHaveErrors('title_id')
            ->assertSessionHasErrors('translation_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Search across locales
    |--------------------------------------------------------------------------
    */

    public function test_search_matches_indonesian_term(): void
    {
        $this->banner(['title_en' => 'Integrity', 'title_id' => 'Integritas']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.hero-banners.index', ['search' => 'Integritas']))
            ->assertOk()
            ->assertSee('Integrity');
    }
}
