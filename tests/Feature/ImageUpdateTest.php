<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function makeService(): Service
    {
        $cat = ServiceCategory::create(['name' => 'Cat', 'slug' => 'cat', 'status' => 'active', 'display_order' => 1]);

        return Service::create([
            'category_id' => $cat->id,
            'name_en' => 'Svc',
            'slug' => 'svc',
            'image' => 'services/old.jpg',
            'status' => 'published',
            'is_featured' => false,
        ]);
    }

    private function payload(Service $service, array $overrides = []): array
    {
        return array_merge([
            'category_id' => $service->category_id,
            'name_en' => 'Svc',
            'status' => 'published',
        ], $overrides);
    }

    /**
     * The exact reported bug: click "Delete Image" then upload a new one.
     * remove_image=1 + a new file → the NEW image must be saved (not nulled).
     */
    public function test_new_image_is_saved_even_when_remove_flag_is_set(): void
    {
        Storage::fake('public');
        $service = $this->makeService();

        $response = $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.services.update', $service), $this->payload($service, [
                'remove_image' => 1,
                'image' => UploadedFile::fake()->image('new.jpg'),
            ]));

        $response->assertRedirect(route('admin.services.index'));

        $fresh = $service->fresh();
        $this->assertNotNull($fresh->image, 'Image should NOT be null when a new file is uploaded.');
        $this->assertNotSame('services/old.jpg', $fresh->image, 'Image should be the newly uploaded file.');
        Storage::disk('public')->assertExists($fresh->image);
    }

    /**
     * Pure delete (no new file): remove_image=1 → image becomes null.
     */
    public function test_remove_image_without_new_file_clears_image(): void
    {
        Storage::fake('public');
        $service = $this->makeService();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.services.update', $service), $this->payload($service, ['remove_image' => 1]))
            ->assertRedirect();

        $this->assertNull($service->fresh()->image);
    }

    /**
     * Normal replace (no remove flag): new file replaces old.
     */
    public function test_uploading_new_image_replaces_old(): void
    {
        Storage::fake('public');
        $service = $this->makeService();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.services.update', $service), $this->payload($service, [
                'image' => UploadedFile::fake()->image('replace.jpg'),
            ]))
            ->assertRedirect();

        $fresh = $service->fresh();
        $this->assertNotNull($fresh->image);
        $this->assertNotSame('services/old.jpg', $fresh->image);
    }
}
