<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Project i18n coverage, mirroring the Service gold standard: trait resolution +
 * fallback, per-locale validation (EN required, ID all-or-nothing), translation
 * progress, localized public rendering, config-controlled slug, and search.
 */
class ProjectI18nTest extends TestCase
{
    use RefreshDatabase;

    private function service(): Service
    {
        $cat = ServiceCategory::create(['name' => 'Cat', 'slug' => 'cat', 'status' => 'active', 'display_order' => 1]);

        return Service::create([
            'category_id' => $cat->id, 'name_en' => 'Survey', 'slug' => 'survey',
            'status' => 'published', 'is_featured' => false,
        ]);
    }

    private function published(array $attributes = []): Project
    {
        return Project::create(array_merge([
            'name_en' => 'Coastal Mapping',
            'slug' => 'coastal-mapping',
            'status' => 'completed', // public-visible
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
        $this->published(['name_en' => 'Harbor Survey', 'name_id' => null, 'description_en' => '<p>EN</p>']);

        $this->get('/projects/coastal-mapping')->assertOk()->assertSee('Harbor Survey');
        $this->get('/id/projects/coastal-mapping')->assertOk()->assertSee('Harbor Survey');
    }

    public function test_public_page_shows_indonesian_when_available(): void
    {
        $this->published(['name_en' => 'Harbor Survey', 'name_id' => 'Survei Pelabuhan']);

        $this->get('/projects/coastal-mapping')->assertOk()->assertSee('Harbor Survey')->assertDontSee('Survei Pelabuhan');
        $this->get('/id/projects/coastal-mapping')->assertOk()->assertSee('Survei Pelabuhan');
    }

    public function test_translation_progress_reflects_completeness(): void
    {
        $project = $this->published([
            'name_en' => 'A', 'description_en' => '<p>B</p>',
            'name_id' => 'A-id', 'description_id' => null,
        ]);

        $this->assertSame(50, $project->translationProgress('id'));
        $this->assertFalse($project->isTranslated('id'));

        $project->update(['description_id' => '<p>B-id</p>']);
        $this->assertTrue($project->fresh()->isTranslated('id'));
    }

    /*
    |--------------------------------------------------------------------------
    | Admin form rendering
    |--------------------------------------------------------------------------
    */

    public function test_admin_create_form_renders_language_tabs(): void
    {
        $this->service();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.projects.create'))
            ->assertOk()
            ->assertSee('Project Name (EN)')
            ->assertSee('Project Name (ID)');
    }

    public function test_admin_edit_form_prefills_localized_values(): void
    {
        $project = $this->published(['name_en' => 'Harbor Survey', 'name_id' => 'Survei Pelabuhan']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.projects.edit', $project))
            ->assertOk()
            ->assertSee('Harbor Survey')
            ->assertSee('Survei Pelabuhan');
    }

    /*
    |--------------------------------------------------------------------------
    | Per-locale validation
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_project_with_both_locales(): void
    {
        $service = $this->service();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.projects.store'), [
                'service_ids' => [$service->id],
                'name_en' => 'Bridge Inspection',
                'name_id' => 'Inspeksi Jembatan',
                'description_en' => '<p>EN</p>',
                'description_id' => '<p>ID</p>',
                'status' => 'completed',
            ])
            ->assertRedirect(route('admin.projects.index'));

        $this->assertDatabaseHas('projects', [
            'name_en' => 'Bridge Inspection',
            'name_id' => 'Inspeksi Jembatan',
            'slug' => 'bridge-inspection',
        ]);
    }

    public function test_english_name_is_required(): void
    {
        $service = $this->service();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.projects.store'), [
                'service_ids' => [$service->id],
                'name_en' => '',
                'status' => 'completed',
            ])
            ->assertSessionHasErrors('name_en');
    }

    public function test_indonesian_is_optional_when_untouched(): void
    {
        $service = $this->service();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.projects.store'), [
                'service_ids' => [$service->id],
                'name_en' => 'English Only',
                'description_en' => '<p>EN</p>',
                'status' => 'completed',
            ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.projects.index'));
    }

    public function test_partial_indonesian_translation_is_rejected(): void
    {
        $service = $this->service();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.projects.store'), [
                'service_ids' => [$service->id],
                'name_en' => 'Survey',
                'description_en' => '<p>EN</p>',
                'name_id' => 'Survei',
                'description_id' => '', // <-- missing → must fail
                'status' => 'completed',
            ])
            ->assertSessionHasErrors('description_id')
            ->assertSessionDoesntHaveErrors('name_id')
            ->assertSessionHasErrors('translation_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Slug behaviour (config-controlled)
    |--------------------------------------------------------------------------
    */

    public function test_slug_regenerates_when_name_en_changes_and_enabled(): void
    {
        config(['cms.auto_regenerate_slug' => true]);
        $service = $this->service();
        $project = $this->published(['name_en' => 'Old Name', 'slug' => 'old-name']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.projects.update', $project), [
                'service_ids' => [$service->id],
                'name_en' => 'New Name',
                'status' => 'completed',
            ])
            ->assertRedirect();

        $this->assertSame('new-name', $project->fresh()->slug);
    }

    public function test_slug_is_frozen_when_auto_regenerate_disabled(): void
    {
        config(['cms.auto_regenerate_slug' => false]);
        $service = $this->service();
        $project = $this->published(['name_en' => 'Old Name', 'slug' => 'old-name']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.projects.update', $project), [
                'service_ids' => [$service->id],
                'name_en' => 'Completely Different',
                'status' => 'completed',
            ])
            ->assertRedirect();

        $this->assertSame('old-name', $project->fresh()->slug);
    }

    /*
    |--------------------------------------------------------------------------
    | Search across locales / facets
    |--------------------------------------------------------------------------
    */

    public function test_search_matches_indonesian_term_and_facets(): void
    {
        $this->published([
            'name_en' => 'Consulting', 'name_id' => 'Konsultasi',
            'slug' => 'unique-marker', 'client_name' => 'PT Nusantara',
        ]);

        $admin = Admin::factory()->create();

        foreach (['Konsultasi', 'unique-marker', 'PT Nusantara', 'completed'] as $term) {
            $this->actingAs($admin, 'admin')
                ->get(route('admin.projects.index', ['search' => $term]))
                ->assertOk()
                ->assertSee('Consulting');
        }
    }
}
