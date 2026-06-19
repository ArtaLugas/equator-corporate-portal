<?php

namespace App\Providers;

use App\Models\AboutContent;
use App\Models\AboutSection;
use App\Models\CompanyDocument;
use App\Models\CoreValue;
use App\Models\HeroBanner;
use App\Models\KeyMetric;
use App\Models\Partner;
use App\Models\Project;
use App\Models\Service;
use App\Observers\HomeContentCacheObserver;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Models whose changes must invalidate the cached homepage content.
     */
    private const HOME_CONTENT_MODELS = [
        HeroBanner::class,
        KeyMetric::class,
        Service::class,
        Project::class,
        CoreValue::class,
        Partner::class,
        AboutSection::class,
        AboutContent::class,
        CompanyDocument::class,
    ];

    /**
     * Register model observers.
     */
    public function boot(): void
    {
        foreach (self::HOME_CONTENT_MODELS as $model) {
            $model::observe(HomeContentCacheObserver::class);
        }
    }
}
