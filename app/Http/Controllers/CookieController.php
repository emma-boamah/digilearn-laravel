<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\CookieManager;
use App\Models\CookieConsent;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;

class CookieController extends Controller
{
    protected $cookieManager;

    public function __construct(CookieManager $cookieManager)
    {
        $this->cookieManager = $cookieManager;
    }

    /**
     * Get current cookie consent status
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'has_consent' => $this->cookieManager->hasConsent(),
            'consent' => $this->cookieManager->getConsent(),
            'categories' => $this->cookieManager->getCategories(),
        ]);
    }

    /**
     * Set cookie consent preferences
     */
    public function setConsent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'preference' => 'boolean',
            'analytics' => 'boolean',
            'consent' => 'boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid consent data',
                'errors' => $validator->errors()
            ], 422);
        }

        $consent = $request->only(['preference', 'analytics', 'consent']);
        $gpsData = $request->only(['latitude', 'longitude', 'country', 'city', 'region']);
        $this->cookieManager->setConsent($consent, $gpsData);

        return response()->json([
            'success' => true,
            'message' => 'Cookie preferences updated successfully',
            'consent' => $this->cookieManager->getConsent()
        ]);
    }

    /**
     * Accept all cookies
     */
    public function acceptAll(Request $request): JsonResponse
    {
        $gpsData = $request->only(['latitude', 'longitude', 'country', 'city', 'region']);
        $this->cookieManager->acceptAllWithGPS($gpsData);

        return response()->json([
            'success' => true,
            'message' => 'All cookies accepted',
            'consent' => $this->cookieManager->getConsent()
        ]);
    }

    /**
     * Reject non-essential cookies
     */
    public function rejectAll(Request $request): JsonResponse
    {
        $gpsData = $request->only(['latitude', 'longitude', 'country', 'city', 'region']);
        $this->cookieManager->rejectAllWithGPS($gpsData);

        return response()->json([
            'success' => true,
            'message' => 'Non-essential cookies rejected',
            'consent' => $this->cookieManager->getConsent()
        ]);
    }

    /**
     * Get cookie statistics (admin only)
     */
    public function stats(): JsonResponse
    {
        // Authorization is handled by route middleware ['auth', 'admin']

        return response()->json([
            'stats' => CookieConsent::getConsentStats(),
            'manager_stats' => $this->cookieManager->getStats()
        ]);
    }

    /**
     * Delete all user cookies
     */
    public function deleteAll(): JsonResponse
    {
        // Clear all cookies except essential ones
        $cookies = request()->cookies->all();

        foreach ($cookies as $name => $value) {
            if (!in_array($name, ['laravel_session', 'XSRF-TOKEN', 'cookie_consent'])) {
                Cookie::queue(Cookie::forget($name));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'All cookies deleted'
        ]);
    }

    /**
     * Get cookie policy page
     */
    public function policy()
    {
        return view('cookies.policy', [
            'categories' => $this->cookieManager->getCategories(),
            'stats' => $this->cookieManager->getStats()
        ]);
    }

    /**
     * Get cookie settings page
     */
    public function settings()
    {
        return view('cookies.settings', [
            'consent' => $this->cookieManager->getConsent(),
            'categories' => $this->cookieManager->getCategories(),
            'has_consent' => $this->cookieManager->hasConsent()
        ]);
    }

    /**
     * Admin cookie statistics page
     */
    public function adminStatsPage()
    {
        $stats = \App\Models\CookieConsent::getConsentStats();
        $managerStats = $this->cookieManager->getStats();

        // Get recent consents (without user relationships since they may not exist)
        $recentConsents = \App\Models\CookieConsent::recent(30)
            ->orderBy('consented_at', 'desc')
            ->take(20)
            ->get();

        // Get consent trends for the last 7 days
        $consentTrends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = \App\Models\CookieConsent::whereDate('consented_at', $date)->count();
            $consentTrends[] = [
                'date' => $date->format('M d'),
                'consents' => $count
            ];
        }

        return view('admin.cookie-stats', compact('stats', 'managerStats', 'recentConsents', 'consentTrends'));
    }
}