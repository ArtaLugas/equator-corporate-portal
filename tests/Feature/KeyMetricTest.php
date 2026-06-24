<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\KeyMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeyMetricTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_metric(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.key-metrics.store'), [
                'value' => '300+',
                'label_en' => 'Happy Clients',
                'icon' => 'bi bi-emoji-smile',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.key-metrics.index'));

        $this->assertDatabaseHas('key_metrics', ['value' => '300+', 'label_en' => 'Happy Clients']);
    }

    public function test_metric_requires_value_and_label(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.key-metrics.store'), ['status' => 'active'])
            ->assertSessionHasErrors(['value', 'label_en']);
    }

    public function test_homepage_uses_cms_metrics_when_present(): void
    {
        KeyMetric::create(['value' => '999+', 'label_en' => 'Custom Metric', 'status' => 'active', 'display_order' => 1]);

        $this->get(route('home'))->assertOk()->assertSee('Custom Metric')->assertSee('999+');
    }

    public function test_homepage_falls_back_to_defaults_when_no_metrics(): void
    {
        // No KeyMetric rows → default labels rendered.
        $this->get(route('home'))->assertOk()->assertSee('Years Experience');
    }

    public function test_inactive_metric_not_shown_on_homepage(): void
    {
        KeyMetric::create(['value' => '111', 'label_en' => 'Hidden Metric', 'status' => 'inactive', 'display_order' => 1]);

        // Inactive ignored → fallback defaults shown, hidden metric absent.
        $response = $this->get(route('home'))->assertOk();
        $response->assertDontSee('Hidden Metric');
        $response->assertSee('Years Experience');
    }
}
