<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Database\Seeders\Concerns\LoadsSeedData;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    use LoadsSeedData;

    public function run(): void
    {
        $order = 1;

        foreach ($this->loadData('service_categories') as $row) {
            ServiceCategory::updateOrCreate(
                ['slug' => $this->clip($row['slug'])],
                [
                    'name_en' => $this->clip($row['category_name']),
                    'description_en' => $this->nullable($row['description'] ?? null),
                    'image' => $this->nullable($row['image'] ?? null),
                    'display_order' => $order++,
                    'status' => $row['status'] === 'active' ? 'active' : 'inactive',
                ]
            );
        }
    }
}
