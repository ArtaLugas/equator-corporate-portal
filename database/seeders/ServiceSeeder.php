<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Database\Seeders\Concerns\LoadsSeedData;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    use LoadsSeedData;

    public function run(): void
    {
        // Map legacy service_category id → slug, then to the new category id.
        $legacyCatSlug = collect($this->loadData('service_categories'))
            ->pluck('slug', 'id');

        $catIdBySlug = ServiceCategory::pluck('id', 'slug');

        foreach ($this->loadData('detail_services') as $row) {
            $slug = $legacyCatSlug[$row['category_id']] ?? null;
            $categoryId = $slug ? ($catIdBySlug[$slug] ?? null) : null;

            if (! $categoryId) {
                continue; // skip if the parent category wasn't seeded
            }

            Service::updateOrCreate(
                ['slug' => $this->clip($row['slug'])],
                [
                    'category_id' => $categoryId,
                    'name' => $this->clip($row['service_name']),
                    'short_description' => null,
                    'description' => $this->nullable($row['description'] ?? null),
                    'image' => $this->nullable($row['image'] ?? null),
                    'status' => ($row['status'] ?? '') === 'active' ? 'published' : 'draft',
                    'is_featured' => false,
                ]
            );
        }
    }
}
