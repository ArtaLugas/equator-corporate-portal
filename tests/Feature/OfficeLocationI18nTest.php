<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\OfficeLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Office Location i18n coverage, following the gold standard. `name` is the
 * required anchor; `address` is optional plain text. Both render on the public
 * Contact page. No HTML fields.
 */
class OfficeLocationI18nTest extends TestCase
{
    use RefreshDatabase;

    private function location(array $attributes = []): OfficeLocation
    {
        return OfficeLocation::create(array_merge([
            'name_en' => 'Head Office',
            'address_en' => 'Jakarta, Indonesia',
            'phone' => '+62 21 1234 5678',
            'email' => 'office@company.com',
            'display_order' => 1,
            'status' => 'active',
            'is_primary' => true,
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Public rendering & fallback (Contact page)
    |--------------------------------------------------------------------------
    */

    public function test_contact_falls_back_to_english_when_translation_missing(): void
    {
        $this->location(['name_en' => 'Branch Surabaya', 'name_id' => null]);

        $this->get('/contact')->assertOk()->assertSee('Branch Surabaya');
        $this->get('/id/contact')->assertOk()->assertSee('Branch Surabaya'); // fallback
    }

    public function test_contact_shows_indonesian_when_available(): void
    {
        $this->location([
            'name_en' => 'Head Office',
            'name_id' => 'Kantor Pusat',
        ]);

        $this->get('/contact')->assertOk()->assertSee('Head Office')->assertDontSee('Kantor Pusat');
        $this->get('/id/contact')->assertOk()->assertSee('Kantor Pusat');
    }

    public function test_translation_progress_reflects_completeness(): void
    {
        $location = $this->location(['name_id' => 'Kantor Pusat', 'address_id' => null]);

        $this->assertSame(50, $location->translationProgress('id'));

        $location->update(['address_id' => 'Jakarta, Indonesia']);
        $this->assertTrue($location->fresh()->isTranslated('id'));
    }

    /*
    |--------------------------------------------------------------------------
    | Admin form rendering
    |--------------------------------------------------------------------------
    */

    public function test_admin_create_form_renders_language_tabs(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.office-locations.create'))
            ->assertOk()
            ->assertSee('Location Name (EN)')
            ->assertSee('Location Name (ID)');
    }

    public function test_admin_edit_form_prefills_localized_values(): void
    {
        $location = $this->location(['name_en' => 'Head Office', 'name_id' => 'Kantor Pusat']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.office-locations.edit', $location))
            ->assertOk()
            ->assertSee('Head Office')
            ->assertSee('Kantor Pusat');
    }

    /*
    |--------------------------------------------------------------------------
    | Per-locale validation
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_location_with_both_locales(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.office-locations.store'), [
                'name_en' => 'Head Office',
                'name_id' => 'Kantor Pusat',
                'address_en' => 'Jakarta, Indonesia',
                'address_id' => 'Jakarta, Indonesia',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.office-locations.index'));

        $this->assertDatabaseHas('office_locations', [
            'name_en' => 'Head Office',
            'name_id' => 'Kantor Pusat',
        ]);
    }

    public function test_english_name_is_required(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.office-locations.store'), [
                'name_en' => '',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('name_en');
    }

    public function test_indonesian_is_optional_when_untouched(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.office-locations.store'), [
                'name_en' => 'English Only',
                'address_en' => 'Jakarta',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.office-locations.index'));
    }

    public function test_partial_indonesian_translation_is_rejected(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.office-locations.store'), [
                'name_en' => 'Head Office',
                'address_en' => 'Jakarta, Indonesia',
                'name_id' => 'Kantor Pusat',
                'address_id' => '', // <-- missing → must fail
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('address_id')
            ->assertSessionDoesntHaveErrors('name_id')
            ->assertSessionHasErrors('translation_id');
    }
}
