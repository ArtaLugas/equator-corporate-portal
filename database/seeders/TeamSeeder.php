<?php

namespace Database\Seeders;

use App\Models\Team;
use Database\Seeders\Concerns\LoadsSeedData;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    use LoadsSeedData;

    public function run(): void
    {
        foreach ($this->loadData('teams') as $row) {
            Team::updateOrCreate(
                ['name' => $this->clip($row['name']), 'position_en' => $this->clip($row['position'])],
                [
                    'photo' => $this->nullable($row['photo'] ?? null),
                    'email' => $this->nullable($row['email'] ?? null),
                    'linkedin_url' => $this->nullable($row['linkedin'] ?? null),
                    'display_order' => (int) ($row['order_position'] ?? 0),
                    'status' => 'active',
                ]
            );
        }
    }
}
