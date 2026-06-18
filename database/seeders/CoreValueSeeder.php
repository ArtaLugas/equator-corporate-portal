<?php

namespace Database\Seeders;

use App\Models\CoreValue;
use Database\Seeders\Concerns\LoadsSeedData;
use Illuminate\Database\Seeder;

class CoreValueSeeder extends Seeder
{
    use LoadsSeedData;

    public function run(): void
    {
        foreach ($this->loadData('core_values') as $row) {
            CoreValue::updateOrCreate(
                ['title' => $row['value']],
                [
                    'description' => $this->nullable($row['description'] ?? null),
                    'icon' => $this->nullable($row['icon_class'] ?? null),
                    'display_order' => (int) ($row['display_order'] ?? 0),
                    'status' => 'active',
                ]
            );
        }
    }
}
