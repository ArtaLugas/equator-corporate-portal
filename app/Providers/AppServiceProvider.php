<?php

namespace App\Providers;

use App\Mail\Transport\BrevoApiTransport;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
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

        // i18n: locale lives in the URL prefix ({locale?}). Default it to ''
        // globally so the default locale stays unprefixed AND positional
        // route() calls (e.g. route('news.show', $slug)) keep their argument
        // aligned to {slug} in every context — including the unprefixed
        // sitemap, console commands, and tests. SetLocale overrides it to the
        // active non-default locale per request.
        URL::defaults(['locale' => '']);

        // Register the Brevo HTTP API mail transport (used when BREVO_API_KEY is
        // set — see App\Services\BrevoMailConfigurator). Delivers over HTTPS so
        // it survives shared hosting that blocks outbound SMTP.
        Mail::extend('brevo-api', function (array $config) {
            return new BrevoApiTransport((string) ($config['key'] ?? ''));
        });
    }
}
