<?php

namespace App\Providers;

use App\Models\Admin;
use App\Observers\HomeContentCacheObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::viaRequest('admin-session', function () {
            //
        });

        // Paginasi: gunakan tampilan kustom yang elegan & profesional secara global.
        // (defaultSimpleView dibiarkan bawaan Laravel karena view ini memakai $elements/total()).
        Paginator::defaultView('vendor.pagination.equator');

        // Invalidasi cache homepage saat konten terkait berubah.
        foreach ([
            \App\Models\HeroBanner::class,
            \App\Models\KeyMetric::class,
            \App\Models\Service::class,
            \App\Models\Project::class,
            \App\Models\CoreValue::class,
            \App\Models\Partner::class,
            \App\Models\AboutSection::class,
            \App\Models\AboutContent::class,
            \App\Models\CompanyDocument::class,
        ] as $model) {
            $model::observe(HomeContentCacheObserver::class);
        }

        config([
            'session.remember_me_duration' => 10080,
        ]);

        // Only super admins may view the activity log.
        Gate::define('view-activity-logs', fn (Admin $admin) => $admin->isSuperAdmin());

        // Share notification data with the admin topbar.
        // Wrapped defensively so a missing table / transient DB issue degrades
        // gracefully (empty notifications) instead of crashing every admin page.
        View::composer('admin.partials.topbar', function ($view) {
            $unreadCount = 0;
            $items = collect();

            try {
                /** @var \App\Models\Admin|null $admin */
                $admin = auth('admin')->user();

                if ($admin) {
                    $unreadCount = $admin->unreadNotifications()->count();
                    $items = $admin->notifications()->latest()->take(8)->get();
                }
            } catch (\Throwable $e) {
                report($e);
            }

            $view->with([
                'notifUnreadCount' => $unreadCount,
                'notifItems' => $items,
            ]);
        });
    }
}
