<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Faq;
use App\Models\Team;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniqueDisplayOrderTest extends TestCase
{
    use RefreshDatabase;

    /* ---------------- Standalone table (faqs) ---------------- */

    public function test_db_rejects_duplicate_display_order_on_standalone_table(): void
    {
        Faq::create(['question' => 'Q1', 'answer' => 'A1', 'display_order' => 5]);

        $this->expectException(QueryException::class);
        Faq::create(['question' => 'Q2', 'answer' => 'A2', 'display_order' => 5]);
    }

    public function test_validation_rejects_duplicate_display_order(): void
    {
        Faq::create(['question' => 'Q1', 'answer' => 'A1', 'display_order' => 5]);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.faqs.store'), ['question' => 'Q2', 'answer' => 'A2', 'display_order' => 5])
            ->assertSessionHasErrors('display_order');
    }

    /* ---------------- Soft-delete table (teams) ---------------- */

    public function test_db_rejects_duplicate_display_order_among_active_teams(): void
    {
        Team::create(['name' => 'A', 'position' => 'P', 'display_order' => 1, 'status' => 'active']);

        $this->expectException(QueryException::class);
        Team::create(['name' => 'B', 'position' => 'P', 'display_order' => 1, 'status' => 'active']);
    }

    public function test_trashed_team_does_not_block_display_order_reuse(): void
    {
        $a = Team::create(['name' => 'A', 'position' => 'P', 'display_order' => 1, 'status' => 'active']);
        $a->delete(); // soft delete → active_order becomes NULL

        // Reusing order 1 must now succeed (trashed row excluded).
        $b = Team::create(['name' => 'B', 'position' => 'P', 'display_order' => 1, 'status' => 'active']);

        $this->assertDatabaseHas('teams', ['id' => $b->id, 'display_order' => 1, 'deleted_at' => null]);
        $this->assertSoftDeleted('teams', ['id' => $a->id]);
    }
}
