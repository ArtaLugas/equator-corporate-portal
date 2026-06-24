<?php

namespace Database\Seeders;

use App\Models\AboutHistory;
use Database\Seeders\Concerns\LoadsSeedData;
use Illuminate\Database\Seeder;

class AboutHistorySeeder extends Seeder
{
    use LoadsSeedData;

    public function run(): void
    {
        foreach ($this->loadData('histories') as $row) {
            AboutHistory::updateOrCreate(
                ['year' => $row['year'], 'title_en' => $row['title']],
                [
                    'description_en' => $this->nullable($row['description'] ?? null),
                    'image' => $this->nullable($row['image'] ?? null),
                    'display_order' => (int) ($row['display_order'] ?? 0),
                    'status' => 'active',
                ]
            );
        }
    }
}
