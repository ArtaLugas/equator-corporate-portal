<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsCategory;
use Database\Seeders\Concerns\LoadsSeedData;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    use LoadsSeedData;

    public function run(): void
    {
        $legacyCatSlug = collect($this->loadData('news_categories'))->pluck('slug', 'id');
        $catIdBySlug = NewsCategory::pluck('id', 'slug');

        foreach ($this->loadData('news') as $row) {
            $slug = $legacyCatSlug[$row['news_category_id']] ?? null;
            $categoryId = $slug ? ($catIdBySlug[$slug] ?? null) : null;

            if (! $categoryId) {
                continue;
            }

            News::updateOrCreate(
                ['slug' => $this->clip($row['slug'])],
                [
                    'category_id' => $categoryId,
                    'title_en' => $this->clip($row['news_title']),
                    'content_en' => $this->nullable($row['news_content'] ?? null),
                    'image' => $this->nullable($row['news_image'] ?? null),
                    'status' => ($row['status'] ?? '') === 'active' ? 'published' : 'draft',
                    'published_at' => $this->nullable($row['news_published_at'] ?? null),
                    'views_count' => 0,
                    'is_featured' => false,
                ]
            );
        }
    }
}
