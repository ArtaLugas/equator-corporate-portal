<?php

use App\Http\Middleware\AdminAuthenticate;
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
        ]);

        // Track public page views for visitor analytics (appended so it runs
        // after the session is started).
        $middleware->web(append: [
            TrackVisitor::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
