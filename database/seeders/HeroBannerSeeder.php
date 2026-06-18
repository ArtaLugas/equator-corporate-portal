<?php

namespace Database\Seeders;

use App\Models\HeroBanner;
use Database\Seeders\Concerns\LoadsSeedData;
use Illuminate\Database\Seeder;

class HeroBannerSeeder extends Seeder
{
    use LoadsSeedData;

    public function run(): void
    {
        $order = 1;

        foreach ($this->loadData('hero_banners') as $row) {
            HeroBanner::updateOrCreate(
                ['title' => $row['title']],
                [
                    'subtitle' => $this->nullable($row['description'] ?? null),
                    'image' => $this->nullable($row['background_image'] ?? null),
                    'button_text' => $this->nullable($row['button_text'] ?? null),
                    'button_link' => $this->nullable($row['button_link'] ?? null),
                    'display_order' => $order++,
                    'status' => ($row['status'] ?? 'active') === 'active' ? 'active' : 'inactive',
                ]
            );
        }
    }
}
