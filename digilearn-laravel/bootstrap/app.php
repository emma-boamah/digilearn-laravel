<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckWebsiteLock;
use App\Console\Commands\ClearEmailVerificationCache;
use App\Http\Middleware\SuperuserMiddleware;
use App\Http\Middleware\TrackUsersActivity;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Web middleware group
        $middleware->web(append: [
            // Add web-specific middleware here if needed
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ]);
        // Global middleware (applies to all requests)
        $middleware->append(\Illuminate\Session\Middleware\StartSession::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->append(CheckWebsiteLock::class);
        $middleware->append(TrackUsersActivity::class);
        
        // API middleware group
        $middleware->api(append: [
            // Add API-specific middleware here if needed
        ]);

        // Named middleware aliases
        $middleware->alias([
            'security.headers' => SecurityHeaders::class,
            'admin' => AdminMiddleware::class,
            'check.lock' => CheckWebsiteLock::class,
            'superuser' => SuperuserMiddleware::class,
        ]);

        // Rate limiting configuration
        $middleware->throttleApi();
        $middleware->throttleWithRedis();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withCommands([
        // Register your custom Artisan commands here
        ClearEmailVerificationCache::class,
    ])
    ->create();
