<?php

namespace Database\Seeders;

use App\Models\NewsCategory;
use Database\Seeders\Concerns\LoadsSeedData;
use Illuminate\Database\Seeder;

class NewsCategorySeeder extends Seeder
{
    use LoadsSeedData;

    public function run(): void
    {
        foreach ($this->loadData('news_categories') as $row) {
            NewsCategory::updateOrCreate(
                ['slug' => $row['slug']],
                ['name' => $row['name']]
            );
        }
    }
}
