<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * After a successful edit, the admin should return to the exact paginated/filtered
 * list page they came from (via a guarded `return_url`), not the start of the index.
 */
class ProjectEditRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function makeProject(): Project
    {
        $cat = ServiceCategory::create(['name' => 'Cat', 'slug' => 'cat', 'status' => 'active', 'display_order' => 1]);
        $service = Service::create([
            'category_id' => $cat->id, 'name_en' => 'Svc', 'slug' => 'svc',
            'status' => 'published', 'is_featured' => false,
        ]);
        $project = Project::create([
            'name_en' => 'Original', 'slug' => 'original', 'status' => 'completed', 'is_featured' => false,
        ]);
        $project->services()->sync([$service->id]);

        return $project;
    }

    private function payload(Project $project, array $overrides = []): array
    {
        return array_merge([
            'name_en' => 'Updated',
            'service_ids' => $project->services->pluck('id')->all(),
            'status' => 'completed',
        ], $overrides);
    }

    public function test_update_redirects_back_to_the_originating_list_page(): void
    {
        $project = $this->makeProject();
        $returnUrl = route('admin.projects.index').'?page=2&country=Indonesia';

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.projects.update', $project), $this->payload($project, ['return_url' => $returnUrl]))
            ->assertRedirect($returnUrl);
    }

    /** A tampered/foreign return_url must be ignored — no open redirect. */
    public function test_update_ignores_foreign_return_url(): void
    {
        $project = $this->makeProject();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.projects.update', $project), $this->payload($project, [
                'return_url' => 'https://evil.example.com/phish',
            ]))
            ->assertRedirect(route('admin.projects.index'));
    }

    /** No return_url (edit opened directly) → plain index. */
    public function test_update_without_return_url_uses_plain_index(): void
    {
        $project = $this->makeProject();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.projects.update', $project), $this->payload($project))
            ->assertRedirect(route('admin.projects.index'));
    }

    /**
     * The same guarded return-to-list behaviour is wired across all admin
     * modules via guarded_list_url() — Service is a representative cross-check
     * that the pattern generalises beyond Projects.
     */
    public function test_pattern_generalises_to_other_modules_service(): void
    {
        $cat = ServiceCategory::create(['name' => 'Cat', 'slug' => 'cat', 'status' => 'active', 'display_order' => 1]);
        $service = Service::create([
            'category_id' => $cat->id, 'name_en' => 'Svc', 'slug' => 'svc',
            'status' => 'published', 'is_featured' => false,
        ]);
        $returnUrl = route('admin.services.index').'?page=3';

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.services.update', $service), [
                'category_id' => $cat->id,
                'name_en' => 'Svc',
                'status' => 'published',
                'return_url' => $returnUrl,
            ])
            ->assertRedirect($returnUrl);
    }
}
