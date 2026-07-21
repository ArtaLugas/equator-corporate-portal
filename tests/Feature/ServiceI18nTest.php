<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reference coverage for the Service i18n pattern: trait resolution + fallback,
 * per-locale validation (EN required, ID all-or-nothing), translation progress,
 * localized public rendering, and cross-locale search.
 */
class ServiceI18nTest extends TestCase
{
    use RefreshDatabase;

    private function category(): ServiceCategory
    {
        return ServiceCategory::create([
            'name_en' => 'Cat', 'slug' => 'cat', 'status' => 'active', 'display_order' => 1,
        ]);
    }

    private function published(array $attributes = []): Service
    {
        return Service::create(array_merge([
            'category_id' => $this->category()->id,
            'name_en' => 'Topographic Survey',
            'slug' => 'topographic-survey',
            'status' => 'published',
            'is_featured' => false,
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Trait resolution & fallback
    |--------------------------------------------------------------------------
    */

    public function test_public_page_falls_back_to_english_when_translation_missing(): void
    {
        $this->published(['name_en' => 'Land Survey', 'name_id' => null, 'description_en' => '<p>EN body</p>']);

        // English (default, unprefixed)
        $this->get('/services/topographic-survey')->assertOk()->assertSee('Land Survey');

        // Indonesian prefix but no ID translation → falls back to English
        $this->get('/id/services/topographic-survey')->assertOk()->assertSee('Land Survey');
    }

    public function test_public_page_shows_indonesian_when_available(): void
    {
        $this->published(['name_en' => 'Land Survey', 'name_id' => 'Survei Lahan']);

        $this->get('/services/topographic-survey')->assertOk()->assertSee('Land Survey')->assertDontSee('Survei Lahan');
        $this->get('/id/services/topographic-survey')->assertOk()->assertSee('Survei Lahan');
    }

    public function test_translation_progress_reflects_completeness(): void
    {
        // EN has 2 fields filled; ID has 1 of them → 50%.
        $service = $this->published([
            'name_en' => 'A', 'description_en' => '<p>B</p>',
            'name_id' => 'A-id', 'description_id' => null,
        ]);

        $this->assertSame(100, $service->translationProgress('en'));
        $this->assertSame(50, $service->translationProgress('id'));
        $this->assertFalse($service->isTranslated('id'));

        $service->update(['description_id' => '<p>B-id</p>']);
        $this->assertSame(100, $service->fresh()->translationProgress('id'));
        $this->assertTrue($service->fresh()->isTranslated('id'));
    }

    /*
    |--------------------------------------------------------------------------
    | Admin form rendering (language tabs + localized fields)
    |--------------------------------------------------------------------------
    */

    public function test_admin_create_form_renders_language_tabs(): void
    {
        $this->category();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.services.create'))
            ->assertOk()
            ->assertSee('Service Name (EN)')
            ->assertSee('Service Name (ID)');
    }

    public function test_admin_edit_form_prefills_localized_values(): void
    {
        $service = $this->published(['name_en' => 'Land Survey', 'name_id' => 'Survei Lahan']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.services.edit', $service))
            ->assertOk()
            ->assertSee('Land Survey')
            ->assertSee('Survei Lahan');
    }

    /*
    |--------------------------------------------------------------------------
    | Per-locale validation
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_service_with_both_locales(): void
    {
        $cat = $this->category();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.services.store'), [
                'category_id' => $cat->id,
                'name_en' => 'Drone Mapping',
                'name_id' => 'Pemetaan Drone',
                'description_en' => '<p>EN</p>',
                'description_id' => '<p>ID</p>',
                'status' => 'published',
            ])
            ->assertRedirect(route('admin.services.index'));

        $this->assertDatabaseHas('services', [
            'name_en' => 'Drone Mapping',
            'name_id' => 'Pemetaan Drone',
            'slug' => 'drone-mapping', // generated from name_en
        ]);
    }

    public function test_english_name_is_required(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.services.store'), [
                'category_id' => $this->category()->id,
                'name_en' => '',
                'status' => 'published',
            ])
            ->assertSessionHasErrors('name_en');
    }

    public function test_indonesian_is_optional_when_untouched(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.services.store'), [
                'category_id' => $this->category()->id,
                'name_en' => 'English Only',
                'description_en' => '<p>EN</p>',
                // no ID fields at all
                'status' => 'published',
            ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.services.index'));
    }

    public function test_partial_indonesian_translation_is_rejected(): void
    {
        // EN has name + description; ID started (name_id) but description_id left blank.
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.services.store'), [
                'category_id' => $this->category()->id,
                'name_en' => 'Survey',
                'description_en' => '<p>EN</p>',
                'name_id' => 'Survei',
                'description_id' => '', // <-- missing → must fail
                'status' => 'published',
            ])
            // The incomplete field is marked...
            ->assertSessionHasErrors('description_id')
            ->assertSessionDoesntHaveErrors('name_id')
            // ...and one clear locale-level summary is shown.
            ->assertSessionHasErrors('translation_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Slug behaviour (config-controlled auto-regeneration)
    |--------------------------------------------------------------------------
    */

    public function test_slug_regenerates_when_name_en_changes_and_enabled(): void
    {
        config(['cms.auto_regenerate_slug' => true]);
        $service = $this->published(['name_en' => 'Old Name', 'slug' => 'old-name']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.services.update', $service), [
                'category_id' => $service->category_id,
                'name_en' => 'New Name',
                'status' => 'published',
            ])
            ->assertRedirect();

        $this->assertSame('new-name', $service->fresh()->slug);
    }

    public function test_slug_is_frozen_when_auto_regenerate_disabled(): void
    {
        config(['cms.auto_regenerate_slug' => false]);
        $service = $this->published(['name_en' => 'Old Name', 'slug' => 'old-name']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.services.update', $service), [
                'category_id' => $service->category_id,
                'name_en' => 'Completely Different',
                'status' => 'published',
            ])
            ->assertRedirect();

        $this->assertSame('old-name', $service->fresh()->slug);
    }

    public function test_search_covers_slug_category_and_status(): void
    {
        $cat = ServiceCategory::create([
            'name_en' => 'Geospatial', 'slug' => 'geospatial', 'status' => 'active', 'display_order' => 1,
        ]);
        Service::create([
            'category_id' => $cat->id, 'name_en' => 'Hidden Title',
            'slug' => 'unique-marker-slug', 'status' => 'draft', 'is_featured' => false,
        ]);

        // Decoy in another category — every assertion below must exclude it, otherwise
        // a search that matches everything would still look like it "works".
        $other = ServiceCategory::create([
            'name_en' => 'Marine', 'slug' => 'marine', 'status' => 'active', 'display_order' => 2,
        ]);
        Service::create([
            'category_id' => $other->id, 'name_en' => 'Decoy Service',
            'slug' => 'decoy-service', 'status' => 'published', 'is_featured' => false,
        ]);

        $admin = Admin::factory()->create();

        // by slug
        $this->actingAs($admin, 'admin')
            ->get(route('admin.services.index', ['search' => 'unique-marker-slug']))
            ->assertOk()->assertSee('Hidden Title')->assertDontSee('Decoy Service');

        // by category name
        $this->actingAs($admin, 'admin')
            ->get(route('admin.services.index', ['search' => 'Geospatial']))
            ->assertOk()->assertSee('Hidden Title')->assertDontSee('Decoy Service');

        // by status
        $this->actingAs($admin, 'admin')
            ->get(route('admin.services.index', ['search' => 'draft']))
            ->assertOk()->assertSee('Hidden Title')->assertDontSee('Decoy Service');
    }

    /*
    |--------------------------------------------------------------------------
    | Search across locales
    |--------------------------------------------------------------------------
    */

    public function test_search_matches_indonesian_term(): void
    {
        $service = $this->published(['name_en' => 'Consulting', 'name_id' => 'Konsultasi']);

        Service::create([
            'category_id' => $service->category_id, 'name_en' => 'Decoy Service',
            'slug' => 'decoy-service', 'status' => 'published', 'is_featured' => false,
        ]);

        // Admin list search by the Indonesian term finds the row (shown via EN name)
        // and drops everything else.
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.services.index', ['search' => 'Konsultasi']))
            ->assertOk()
            ->assertSee('Consulting')
            ->assertDontSee('Decoy Service');
    }
}
