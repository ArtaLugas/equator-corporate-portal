<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Team i18n coverage, following the gold standard with the agreed design:
 * `name` is single-language; `position` (required anchor) and `bio` (optional
 * plain text) are translatable. Team members render on the (cached) About page.
 */
class TeamI18nTest extends TestCase
{
    use RefreshDatabase;

    private function member(array $attributes = []): Team
    {
        return Team::create(array_merge([
            'name' => 'John Doe',
            'position_en' => 'Chief Executive Officer',
            'display_order' => 1,
            'status' => 'active',
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Public rendering & fallback (About page)
    |--------------------------------------------------------------------------
    */

    public function test_about_position_falls_back_to_english_when_missing(): void
    {
        $this->member(['position_en' => 'Director', 'position_id' => null]);
        Cache::flush();

        $this->get('/about')->assertOk()->assertSee('John Doe')->assertSee('Director');
        $this->get('/id/about')->assertOk()->assertSee('John Doe')->assertSee('Director'); // fallback
    }

    public function test_about_shows_indonesian_position_when_available(): void
    {
        $this->member(['position_en' => 'Director', 'position_id' => 'Direktur']);
        Cache::flush();

        $this->get('/about')->assertOk()->assertSee('Director')->assertDontSee('Direktur');
        $this->get('/id/about')->assertOk()->assertSee('Direktur');
    }

    public function test_name_is_identical_across_locales(): void
    {
        $member = $this->member(['name' => 'Jane Smith', 'position_id' => 'Direktur']);

        // name is not translatable → same value regardless of locale.
        app()->setLocale('en');
        $this->assertSame('Jane Smith', $member->name);
        app()->setLocale('id');
        $this->assertSame('Jane Smith', $member->name);
    }

    public function test_translation_progress_reflects_completeness(): void
    {
        // EN has position + bio; ID has only position → 50%.
        $member = $this->member(['bio_en' => 'A leader.', 'position_id' => 'Direktur', 'bio_id' => null]);

        $this->assertSame(50, $member->translationProgress('id'));

        $member->update(['bio_id' => 'Seorang pemimpin.']);
        $this->assertTrue($member->fresh()->isTranslated('id'));
    }

    /*
    |--------------------------------------------------------------------------
    | Admin form rendering
    |--------------------------------------------------------------------------
    */

    public function test_admin_create_form_renders_language_tabs(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.teams.create'))
            ->assertOk()
            ->assertSee('Position (EN)')
            ->assertSee('Position (ID)');
    }

    public function test_admin_edit_form_prefills_localized_values(): void
    {
        $member = $this->member(['position_en' => 'Director', 'position_id' => 'Direktur']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.teams.edit', $member))
            ->assertOk()
            ->assertSee('Director')
            ->assertSee('Direktur');
    }

    /*
    |--------------------------------------------------------------------------
    | Per-locale validation
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_member_with_both_locales(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.teams.store'), [
                'name' => 'Alex Tan',
                'position_en' => 'Lead Consultant',
                'position_id' => 'Konsultan Utama',
                'bio_en' => 'EN bio',
                'bio_id' => 'Bio ID',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.teams.index'));

        $this->assertDatabaseHas('teams', [
            'name' => 'Alex Tan',
            'position_en' => 'Lead Consultant',
            'position_id' => 'Konsultan Utama',
        ]);
    }

    public function test_english_position_is_required(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.teams.store'), [
                'name' => 'No Position',
                'position_en' => '',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('position_en');
    }

    public function test_indonesian_is_optional_when_untouched(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.teams.store'), [
                'name' => 'English Only',
                'position_en' => 'Manager',
                'bio_en' => 'EN bio',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.teams.index'));
    }

    public function test_partial_indonesian_translation_is_rejected(): void
    {
        // EN has position + bio; ID started (position_id) but bio_id left blank.
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.teams.store'), [
                'name' => 'Partial',
                'position_en' => 'Manager',
                'bio_en' => 'EN bio',
                'position_id' => 'Manajer',
                'bio_id' => '', // <-- missing → must fail
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('bio_id')
            ->assertSessionDoesntHaveErrors('position_id')
            ->assertSessionHasErrors('translation_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Search across locales
    |--------------------------------------------------------------------------
    */

    public function test_search_matches_indonesian_position(): void
    {
        $this->member(['name' => 'John Doe', 'position_id' => 'Direktur']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.teams.index', ['search' => 'Direktur']))
            ->assertOk()
            ->assertSee('John Doe');
    }
}
