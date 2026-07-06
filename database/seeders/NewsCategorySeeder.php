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
            // Seed the default-locale (English) column; Indonesian is filled later
            // via the admin CMS (falls back to English until then).
            NewsCategory::updateOrCreate(
                ['slug' => $row['slug']],
                ['name_en' => $row['name']]
            );
        }
    }
}
