<?php

namespace Database\Seeders;

use App\Models\Partner;
use Database\Seeders\Concerns\LoadsSeedData;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    use LoadsSeedData;

    public function run(): void
    {
        $order = 1;

        foreach ($this->loadData('partners') as $row) {
            Partner::updateOrCreate(
                ['name' => $row['name']],
                [
                    'logo' => $this->nullable($row['logo'] ?? null),
                    'website' => $this->nullable($row['website_url'] ?? null),
                    'display_order' => $order++,
                    'status' => ($row['status'] ?? 'active') === 'active' ? 'active' : 'inactive',
                ]
            );
        }
    }
}
