<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Select-all + bulk "Move to Trash" wired across the soft-delete admin modules.
 * Projects is the representative cross-check (same pattern as Company Credentials).
 */
class BulkTrashTest extends TestCase
{
    use RefreshDatabase;

    private function project(string $slug): Project
    {
        return Project::create([
            'name_en' => ucfirst($slug), 'slug' => $slug, 'status' => 'completed', 'is_featured' => false,
        ]);
    }

    public function test_bulk_destroy_soft_deletes_only_the_selected_rows(): void
    {
        $a = $this->project('alpha');
        $b = $this->project('bravo');
        $keep = $this->project('charlie');

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.projects.bulk-destroy'), ['ids' => [$a->id, $b->id]])
            ->assertRedirect();

        // Selected rows are trashed (soft-deleted, restorable); the unselected one stays.
        $this->assertSoftDeleted('projects', ['id' => $a->id]);
        $this->assertSoftDeleted('projects', ['id' => $b->id]);
        $this->assertNotSoftDeleted('projects', ['id' => $keep->id]);
    }

    public function test_bulk_destroy_with_no_selection_is_a_no_op(): void
    {
        $a = $this->project('alpha');

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.projects.bulk-destroy'), ['ids' => []])
            ->assertRedirect();

        $this->assertNotSoftDeleted('projects', ['id' => $a->id]);
    }
}
