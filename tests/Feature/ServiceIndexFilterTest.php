<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the admin Services index filter bar. The controller has always supported
 * status/category/featured, but the view rendered no controls for them, so the
 * parameters were never submitted and the filters silently did nothing.
 */
class ServiceIndexFilterTest extends TestCase
{
    use RefreshDatabase;

    private function category(string $name, string $slug, int $order = 1): ServiceCategory
    {
        // display_order is unique — each category in a test needs its own.
        return ServiceCategory::create([
            'name_en' => $name, 'slug' => $slug, 'status' => 'active', 'display_order' => $order,
        ]);
    }

    private function service(array $attributes): Service
    {
        return Service::create(array_merge([
            'status' => 'published',
            'is_featured' => false,
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Filter controls are rendered
    |--------------------------------------------------------------------------
    */

    public function test_index_renders_every_filter_control_the_controller_supports(): void
    {
        $survey = $this->category('Survey', 'survey');

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.services.index'))
            ->assertOk()
            ->assertSee('name="search"', false)
            ->assertSee('name="category"', false)
            ->assertSee('name="status"', false)
            ->assertSee('name="featured"', false)
            ->assertSee('name="sort"', false)
            // Category options come from the $categories the controller already passed.
            ->assertSee('value="'.$survey->id.'"', false);
    }

    public function test_active_filter_values_stay_selected_after_submit(): void
    {
        $survey = $this->category('Survey', 'survey');

        $html = $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.services.index', [
                'category' => $survey->id,
                'status' => 'draft',
                'featured' => '0',
            ]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('value="'.$survey->id.'" selected', $html);
        $this->assertStringContainsString('value="draft" selected', $html);
        $this->assertStringContainsString('value="0" selected', $html);
    }

    /*
    |--------------------------------------------------------------------------
    | Filters actually narrow the result set
    |--------------------------------------------------------------------------
    */

    public function test_status_category_and_featured_filters_narrow_the_list(): void
    {
        $survey = $this->category('Survey', 'survey');
        $drilling = $this->category('Drilling', 'drilling', 2);

        $this->service([
            'category_id' => $survey->id, 'name_en' => 'Topographic Survey',
            'slug' => 'topographic-survey', 'status' => 'published', 'is_featured' => true,
        ]);

        $this->service([
            'category_id' => $drilling->id, 'name_en' => 'Core Drilling',
            'slug' => 'core-drilling', 'status' => 'draft',
        ]);

        $admin = Admin::factory()->create();

        // Status
        $this->actingAs($admin, 'admin')
            ->get(route('admin.services.index', ['status' => 'draft']))
            ->assertOk()->assertSee('Core Drilling')->assertDontSee('Topographic Survey');

        // Category
        $this->actingAs($admin, 'admin')
            ->get(route('admin.services.index', ['category' => $survey->id]))
            ->assertOk()->assertSee('Topographic Survey')->assertDontSee('Core Drilling');

        // Featured — including the "0" case, which must not be treated as empty.
        $this->actingAs($admin, 'admin')
            ->get(route('admin.services.index', ['featured' => '1']))
            ->assertOk()->assertSee('Topographic Survey')->assertDontSee('Core Drilling');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.services.index', ['featured' => '0']))
            ->assertOk()->assertSee('Core Drilling')->assertDontSee('Topographic Survey');
    }

    /*
    |--------------------------------------------------------------------------
    | Search narrows (not just matches)
    |--------------------------------------------------------------------------
    */

    public function test_search_excludes_non_matching_rows(): void
    {
        $survey = $this->category('Survey', 'survey');
        $drilling = $this->category('Drilling', 'drilling', 2);

        $this->service([
            'category_id' => $survey->id, 'name_en' => 'Topographic Survey',
            'slug' => 'topographic-survey',
        ]);

        $this->service([
            'category_id' => $drilling->id, 'name_en' => 'Core Drilling',
            'slug' => 'core-drilling',
        ]);

        $admin = Admin::factory()->create();

        // By name.
        $this->actingAs($admin, 'admin')
            ->get(route('admin.services.index', ['search' => 'Topographic']))
            ->assertOk()->assertSee('Topographic Survey')->assertDontSee('Core Drilling');

        // By category name — the orWhereHas branch that used to short-circuit the
        // entire search by OR-ing against its own relation constraint.
        $this->actingAs($admin, 'admin')
            ->get(route('admin.services.index', ['search' => 'Drilling']))
            ->assertOk()->assertSee('Core Drilling')->assertDontSee('Topographic Survey');

        // A term nothing matches must return an empty list, not the whole table.
        $this->actingAs($admin, 'admin')
            ->get(route('admin.services.index', ['search' => 'zzz-no-such-service']))
            ->assertOk()->assertSee('No services found')
            ->assertDontSee('Topographic Survey')->assertDontSee('Core Drilling');
    }

    /*
    |--------------------------------------------------------------------------
    | Clear-search keeps the other filters
    |--------------------------------------------------------------------------
    */

    public function test_clear_search_link_preserves_the_other_active_filters(): void
    {
        $html = $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.services.index', ['search' => 'survey', 'status' => 'draft']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('status=draft', $html);
        $this->assertStringNotContainsString('search=survey', $html);
    }
}
