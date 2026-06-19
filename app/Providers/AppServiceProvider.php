<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
        // Paginasi: gunakan tampilan kustom yang elegan & profesional secara global.
        // (defaultSimpleView dibiarkan bawaan Laravel karena view ini memakai $elements/total()).
        Paginator::defaultView('vendor.pagination.equator');
    }
}
