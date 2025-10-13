<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\CookieManager;
use App\Models\CookieConsent;
use Illuminate\Support\Facades\Log;

class CookieConsentMiddleware
{
    protected $cookieManager;

    public function __construct(CookieManager $cookieManager)
    {
        $this->cookieManager = $cookieManager;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip middleware for API routes, admin routes, and static assets
        if ($this->shouldSkipMiddleware($request)) {
            return $next($request);
        }

        // Check if user has given consent
        $hasConsent = $this->cookieManager->hasConsent();

        // If no consent, we'll show the banner
        // If consent exists, ensure non-essential cookies are handled properly
        if ($hasConsent) {
            $consent = $this->cookieManager->getConsent();

            // Log consent for compliance
            if (!$this->isConsentLogged($request)) {
                $this->logConsent($request, $consent);
            }

            // Delete non-essential cookies if not allowed
            if (!($consent['analytics'] ?? false)) {
                $this->cookieManager->deleteNonEssential();
            }
        }

        // Add cookie manager to view data for frontend
        view()->share('cookieManager', $this->cookieManager);

        return $next($request);
    }

    /**
     * Check if middleware should be skipped
     */
    protected function shouldSkipMiddleware(Request $request): bool
    {
        // Skip for API routes
        if ($request->is('api/*')) {
            return true;
        }

        // Skip for admin routes
        if ($request->is('admin/*')) {
            return true;
        }

        // Skip for AJAX requests
        if ($request->ajax()) {
            return true;
        }

        // Skip for static assets
        if ($request->is('css/*') || $request->is('js/*') || $request->is('images/*') || $request->is('storage/*')) {
            return true;
        }

        // Skip for health checks and monitoring
        if ($request->is('health') || $request->is('ping')) {
            return true;
        }

        return false;
    }

    /**
     * Check if consent is already logged for this session
     */
    protected function isConsentLogged(Request $request): bool
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        return CookieConsent::where('ip_address', $ip)
            ->where('user_agent', $userAgent)
            ->where('consented_at', '>=', now()->subHours(1))
            ->exists();
    }

    /**
     * Log consent for compliance
     */
    protected function logConsent(Request $request, array $consent): void
    {
        try {
            CookieConsent::create([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'consent_data' => $consent,
                'consent_hash' => md5(json_encode($consent)),
                'consented_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log cookie consent', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'consent' => $consent
            ]);
        }
    }
}