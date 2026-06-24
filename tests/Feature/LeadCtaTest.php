<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Contextual lead CTA (shared <x-public.lead-cta>) on Service & Project detail,
 * including the existing ?service= contact prefill.
 */
class LeadCtaTest extends TestCase
{
    use RefreshDatabase;

    private function service(): Service
    {
        $cat = ServiceCategory::create(['name_en' => 'Advisory', 'slug' => 'advisory', 'status' => 'active', 'display_order' => 1]);

        return Service::create([
            'category_id' => $cat->id, 'name_en' => 'ESIA', 'slug' => 'esia',
            'status' => 'published', 'is_featured' => false,
        ]);
    }

    public function test_service_detail_renders_cta_with_contact_prefill(): void
    {
        $this->service();

        $res = $this->get('/services/esia')->assertOk();

        $res->assertSee(__('services.cta_heading'));   // "Ready to Discuss Your Project?"
        $res->assertSee(__('services.cta_button'));    // "Request Consultation"
        // Reuses the existing prefill mechanism: /contact?service=<service name>.
        $res->assertSee(route('contact', ['service' => 'ESIA']), false);
    }

    public function test_project_detail_renders_cta_with_contact_prefill(): void
    {
        Project::create([
            'name_en' => 'Coastal Restoration', 'slug' => 'coastal-restoration',
            'status' => 'completed', 'is_featured' => false,
        ]);

        $res = $this->get('/projects/coastal-restoration')->assertOk();

        $res->assertSee(__('projects.show_cta_title'));    // "Interested in a Similar Project?"
        $res->assertSee(__('projects.show_cta_discuss'));  // "Contact Our Team" (was hardcoded English)
        $res->assertSee(route('contact', ['service' => 'Coastal Restoration']), false);
    }

    public function test_cta_is_localized_on_indonesian_detail_page(): void
    {
        $this->service();

        $this->get('/id/services/esia')
            ->assertOk()
            ->assertSee(__('services.cta_heading', [], 'id')); // "Siap Mendiskusikan Proyek Anda?"
    }
}
