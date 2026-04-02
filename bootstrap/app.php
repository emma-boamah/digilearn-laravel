<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckWebsiteLock;
use App\Console\Commands\ClearEmailVerificationCache;
use App\Http\Middleware\SuperuserMiddleware;
use App\Http\Middleware\TrackUsersActivity;
use App\Http\Middleware\RealIpMiddleware;
use App\Http\Middleware\ThrottleRequestsWithRedirect;
use App\Http\Middleware\CookieConsentMiddleware;
use App\Http\Middleware\CheckSuspended;
use App\Http\Middleware\DecodeObfuscatedIds;
use App\Http\Middleware\HandleJsonRequestErrors;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Global middleware (applies to all requests) - Order matters!
        $middleware->append(RealIpMiddleware::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->append(CheckWebsiteLock::class);
        $middleware->append(HandleJsonRequestErrors::class);

        // Web middleware group
        $middleware->web(append: [
            // ShareErrorsFromSession must come AFTER StartSession
            ShareErrorsFromSession::class,
            CookieConsentMiddleware::class,
            CheckSuspended::class,
            TrackUsersActivity::class,
        ]);
        
        // API middleware group
        $middleware->api(append: [
            // Add API-specific middleware here if needed
            TrackUsersActivity::class,
        ]);

        // Named middleware aliases
        $middleware->alias([
            'security.headers' => SecurityHeaders::class,
            'admin' => AdminMiddleware::class,
            'check.lock' => CheckWebsiteLock::class,
            'superuser' => SuperuserMiddleware::class,
            'throttle.redirect' => ThrottleRequestsWithRedirect::class,
            'check.suspended' => CheckSuspended::class,
            'decode.obfuscated' => DecodeObfuscatedIds::class,
            'subscribed' => \App\Http\Middleware\EnsureSubscribed::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Rate limiting configuration
        $middleware->throttleApi();
        $middleware->throttleWithRedis();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => 'rate_limit_exceeded',
                    'message' => 'Too many attempts. Please try again later.',
                ], 429);
            }
            
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['email' => 'Too many attempts. Please try again later.']);
        });
    })
    ->withCommands([
        // Register your custom Artisan commands here
        ClearEmailVerificationCache::class,
        \App\Console\Commands\UpdateLessonCompletions::class,
    ])
    ->create();
