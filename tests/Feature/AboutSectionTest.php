<?php

namespace Tests\Feature;

use App\Models\AboutSection;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_section_with_unique_display_order(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.about-sections.store'), [
                'name' => 'Who We Are',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('about_sections', ['name' => 'Who We Are', 'display_order' => 1]);
    }

    public function test_cannot_create_section_with_duplicate_display_order(): void
    {
        AboutSection::create(['name' => 'First', 'slug' => 'first', 'display_order' => 1, 'status' => 'active']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.about-sections.store'), [
                'name' => 'Second',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('display_order');

        $this->assertDatabaseMissing('about_sections', ['name' => 'Second']);
    }

    public function test_update_allows_keeping_own_display_order(): void
    {
        $section = AboutSection::create(['name' => 'Solo', 'slug' => 'solo', 'display_order' => 3, 'status' => 'active']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.about-sections.update', $section), [
                'name' => 'Solo Updated',
                'display_order' => 3,
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertSame('Solo Updated', $section->fresh()->name);
    }

    public function test_update_rejects_display_order_taken_by_another(): void
    {
        AboutSection::create(['name' => 'A', 'slug' => 'a', 'display_order' => 1, 'status' => 'active']);
        $b = AboutSection::create(['name' => 'B', 'slug' => 'b', 'display_order' => 2, 'status' => 'active']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.about-sections.update', $b), [
                'name' => 'B',
                'display_order' => 1, // taken by A
                'status' => 'active',
            ])
            ->assertSessionHasErrors('display_order');

        $this->assertSame(2, $b->fresh()->display_order);
    }
}
