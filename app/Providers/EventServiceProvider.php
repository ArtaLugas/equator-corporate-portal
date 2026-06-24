<?php

namespace App\Providers;

use App\Models\AboutContent;
use App\Models\AboutHistory;
use App\Models\AboutSection;
use App\Models\CompanyCredential;
use App\Models\CompanyCredentialItem;
use App\Models\CompanyDocument;
use App\Models\CoreValue;
use App\Models\HeroBanner;
use App\Models\KeyMetric;
use App\Models\Partner;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Team;
use App\Observers\HomeContentCacheObserver;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Models whose changes must invalidate the cached public payloads
     * (homepage and/or About page).
     */
    private const HOME_CONTENT_MODELS = [
        HeroBanner::class,
        KeyMetric::class,
        Service::class,
        ServiceCategory::class,
        Project::class,
        CoreValue::class,
        Partner::class,
        AboutSection::class,
        AboutContent::class,
        AboutHistory::class,
        Team::class,
        CompanyDocument::class,
        CompanyCredential::class,
        CompanyCredentialItem::class,
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
