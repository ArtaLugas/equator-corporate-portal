<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_load(): void
    {
        foreach (['home', 'about', 'faq', 'services.index', 'projects.index', 'news.index', 'contact'] as $name) {
            $this->get(route($name))->assertOk();
        }
    }

    public function test_service_detail_loads(): void
    {
        $cat = ServiceCategory::create(['name' => 'Cat', 'slug' => 'cat', 'status' => 'active', 'display_order' => 1]);
        $service = Service::create([
            'category_id' => $cat->id, 'name' => 'My Service', 'slug' => 'my-service',
            'description' => '<p>Detail</p>', 'status' => 'published', 'is_featured' => false,
        ]);

        $this->get(route('services.show', $service->slug))->assertOk()->assertSee('My Service');
    }

    public function test_draft_service_returns_404(): void
    {
        $cat = ServiceCategory::create(['name' => 'Cat', 'slug' => 'cat', 'status' => 'active', 'display_order' => 1]);
        $service = Service::create([
            'category_id' => $cat->id, 'name' => 'Hidden', 'slug' => 'hidden', 'status' => 'draft', 'is_featured' => false,
        ]);

        $this->get(route('services.show', $service->slug))->assertNotFound();
    }

    public function test_project_detail_loads(): void
    {
        $project = Project::create([
            'name' => 'Big Project', 'slug' => 'big-project', 'status' => 'completed', 'is_featured' => false,
        ]);

        $this->get(route('projects.show', $project->slug))->assertOk()->assertSee('Big Project');
    }

    public function test_news_detail_loads_and_increments_views(): void
    {
        $cat = NewsCategory::create(['name' => 'Cat', 'slug' => 'cat']);
        $article = News::create([
            'category_id' => $cat->id, 'title' => 'Breaking', 'slug' => 'breaking',
            'content' => '<p>Body</p>', 'status' => 'published', 'published_at' => now(),
            'views_count' => 0, 'is_featured' => false,
        ]);

        $this->get(route('news.show', $article->slug))->assertOk()->assertSee('Breaking');

        $this->assertSame(1, $article->fresh()->views_count);
    }
}
