<?php

use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\CaptureLeadSource;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackVisitor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        /*
        |--------------------------------------------------------------------------
        | Override Default Auth Middleware
        |--------------------------------------------------------------------------
        */

        $middleware->alias([
            'admin.auth' => AdminAuthenticate::class,
            'setlocale' => SetLocale::class,
        ]);

        // The cookie-consent banner sets this cookie client-side (plain JSON), so
        // it must be excluded from Laravel's cookie encryption to stay readable
        // by both JS and the cookie_consent() helper.
        $middleware->encryptCookies(except: [
            'equator_cookie_consent',
        ]);

        // Baseline security headers on every web response, plus visitor
        // tracking (appended so it runs after the session is started).
        $middleware->web(append: [
            SecurityHeaders::class,
            TrackVisitor::class,
            CaptureLeadSource::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
