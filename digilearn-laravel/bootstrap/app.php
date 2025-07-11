<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\AdminMiddleware;
use App\Console\Commands\ClearEmailVerificationCache;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware (applies to all requests)
        $middleware->append(SecurityHeaders::class);

        // Web middleware group
        $middleware->web(append: [
            // Add web-specific middleware here if needed
        ]);
        
        // API middleware group
        $middleware->api(append: [
            // Add API-specific middleware here if needed
        ]);

        // Named middleware aliases
        $middleware->alias([
            'security.headers' => SecurityHeaders::class,
            'admin' => AdminMiddleware::class,
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
