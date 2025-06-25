<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\IpUtils;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure rate limiting with:
     * - IP + email based limits
     * - Whitelisting for schools/businesses
     * - Progressive locking
     * - Environment-based configuration
     */
    protected function configureRateLimiting(): void
    {
        // Login rate limiting
        RateLimiter::for('login', function (Request $request) {
            $ip = $request->ip();
            $email = strtolower($request->input('email', ''));
            
            // Get whitelisted IP ranges from config
            $whitelistedIps = config('security.whitelisted_ips', []);
            
            // Check if IP is whitelisted
            $isWhitelisted = !empty($whitelistedIps) && 
                              IpUtils::checkIp($ip, $whitelistedIps);
            
            // Get rate limit settings from config with fallbacks
            $ipAttempts = config('security.throttle_login_ip_attempts', 5);
            $emailAttempts = config('security.throttle_login_email_attempts', 3);
            $decayMinutes = config('security.throttle_login_decay_minutes', 15);
            
            // Apply higher limits for whitelisted IPs
            if ($isWhitelisted) {
                $ipAttempts = config('security.whitelist_login_ip_attempts', 50);
                $emailAttempts = config('security.whitelist_login_email_attempts', 20);
                $decayMinutes = config('security.whitelist_login_decay_minutes', 1);
            }
            
            // Progressive locking based on failed attempts
            $failedAttempts = $this->getFailedAttempts($request, 'login');
            if ($failedAttempts > 5) {
                return [
                    Limit::perMinute(1)->by($ip . '|login'),
                    Limit::perMinute(1)->by($email . '|login')
                ];
            } elseif ($failedAttempts > 2) {
                return [
                    Limit::perMinutes($decayMinutes, ceil($ipAttempts/2))->by($ip . '|login'),
                    Limit::perMinutes($decayMinutes, ceil($emailAttempts/2))->by($email . '|login')
                ];
            }
            
            // Standard rate limits
            return [
                Limit::perMinutes(
                    config('security.throttle_signup_decay_minutes', 60),
                    config('security.throttle_signup_ip_attempts', 10)
                )->by($request->ip())->response(function() use ($request) {
                    $retryAfter = $this->getRetryAfter($request, 'signup');
                    return response('', 429)
                        ->header('Retry-After', $retryAfter);
                }),
                Limit::perMinutes($decayMinutes, $ipAttempts)->by($ip . '|login'),
                Limit::perMinutes($decayMinutes, $emailAttempts)->by($email . '|login')
            ];
        });

        // Signup rate limiting
        RateLimiter::for('signup', function (Request $request) {
            $ip = $request->ip();
            $email = strtolower($request->input('email', ''));
            
            // Get whitelisted IP ranges from config
            $whitelistedIps = config('security.whitelisted_ips', []);
            
            // Check if IP is whitelisted
            $isWhitelisted = !empty($whitelistedIps) && 
                              IpUtils::checkIp($ip, $whitelistedIps);
            
            // Get rate limit settings from config with fallbacks
            $ipAttempts = config('security.throttle_signup_ip_attempts', 10);
            $emailAttempts = config('security.throttle_signup_email_attempts', 3);
            $decayMinutes = config('security.throttle_signup_decay_minutes', 60);
            
            // Apply higher limits for whitelisted IPs
            if ($isWhitelisted) {
                $ipAttempts = config('security.whitelist_signup_ip_attempts', 100);
                $emailAttempts = config('security.whitelist_signup_email_attempts', 20);
                $decayMinutes = config('security.whitelist_signup_decay_minutes', 1);
            }
            
            // Progressive locking based on failed attempts
            $failedAttempts = $this->getFailedAttempts($request, 'signup');
            if ($failedAttempts > 5) {
                return [
                    Limit::perHour(1)->by($ip . '|signup'),
                    Limit::perHour(1)->by($email . '|signup')
                ];
            }
            
            // Standard rate limits
            return [
                Limit::perMinutes(
                    config('security.throttle_signup_decay_minutes', 60),
                    config('security.throttle_signup_ip_attempts', 10)
                )->by($request->ip())->response(function() use ($request) {
                    $retryAfter = $this->getRetryAfter($request, 'signup');
                    return response('', 429)
                        ->header('Retry-After', $retryAfter);
                }),
                Limit::perMinutes($decayMinutes, $ipAttempts)->by($ip . '|signup'),
                Limit::perMinutes($decayMinutes, $emailAttempts)->by($email . '|signup')
            ];
        });
    }

    /**
     * Get failed attempts from session to implement progressive locking
     */
    protected function getFailedAttempts(Request $request, string $type): int
    {
        return $request->session()->get("{$type}_failed_attempts", 0);
    }

    // Helper method to get retry time
    protected function getRetryAfter(Request $request, string $type): int
    {
        $limiter = app(RateLimiter::class);
        $key = $request->ip() . '|' . $type;
        return $limiter->availableIn($key);
    }
}