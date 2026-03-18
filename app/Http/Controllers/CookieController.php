<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\CookieManager;
use App\Models\CookieConsent;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

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
        Log::info('Cookie consent request received', [
            'all_data' => $request->all(),
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $validator = Validator::make($request->all(), [
            'preference' => 'boolean',
            'analytics' => 'boolean',
            'consent' => 'boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'page_url' => 'nullable|url|max:2048',
        ]);

        if ($validator->fails()) {
            Log::error('Cookie consent validation failed', [
                'errors' => $validator->errors(),
                'data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid consent data',
                'errors' => $validator->errors()
            ], 422);
        }

        $consent = $request->only(['preference', 'analytics', 'consent']);
        $gpsData = $request->only(['latitude', 'longitude', 'country', 'city', 'region', 'page_url']);

        Log::info('Processing cookie consent', [
            'consent' => $consent,
            'gps_data' => $gpsData
        ]);

        $this->cookieManager->setConsent($consent, $gpsData);

        $finalConsent = $this->cookieManager->getConsent();

        Log::info('Cookie consent processed successfully', [
            'final_consent' => $finalConsent
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cookie preferences updated successfully',
            'consent' => $finalConsent
        ]);
    }

    /**
     * Accept all cookies
     */
    public function acceptAll(Request $request): JsonResponse
    {
        $gpsData = $request->only(['latitude', 'longitude', 'country', 'city', 'region', 'page_url']);
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
        $gpsData = $request->only(['latitude', 'longitude', 'country', 'city', 'region', 'page_url']);
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
    public function adminStatsPage(Request $request)
    {
        $stats = CookieConsent::getConsentStats();
        $managerStats = $this->cookieManager->getStats();
        $limit = $request->get('limit', 20);

        // Get recent consents
        $recentConsents = CookieConsent::recent(30)
            ->orderBy('consented_at', 'desc')
            ->take($limit)
            ->get();

        // Get consent trends for the last 7 days
        $consentTrends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = CookieConsent::whereDate('consented_at', $date)->count();
            $consentTrends[] = [
                'date' => $date->format('M d'),
                'consents' => $count
            ];
        }

        $countryBreakdown = CookieConsent::getCountryBreakdown();

        return view('admin.cookie-stats', compact('stats', 'managerStats', 'recentConsents', 'consentTrends', 'countryBreakdown', 'limit'));
    }

    /**
     * Export cookie consent logs as CSV
     */
    public function exportCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="cookie-consent-logs-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, [
                'ID',
                'IP Address',
                'Device',
                'Browser',
                'Country',
                'City',
                'Region',
                'Consent Categories',
                'Page URL',
                'Date Created'
            ]);

            // CSV Data
            CookieConsent::orderBy('consented_at', 'desc')->chunk(100, function ($consents) use ($file) {
                foreach ($consents as $consent) {
                    $categories = [];
                    $data = $consent->consent_data ?? [];
                    
                    if ($data['preference'] ?? false) $categories[] = 'Essential';
                    if ($data['analytics'] ?? false) $categories[] = 'Analytics';
                    if ($data['marketing'] ?? false) $categories[] = 'Marketing'; // Future proofing
                    
                    fputcsv($file, [
                        $consent->id,
                        $consent->ip_address,
                        $consent->device_type,
                        $consent->browser,
                        $consent->country ?? 'Unknown',
                        $consent->city ?? 'Unknown',
                        $consent->region ?? 'Unknown',
                        implode(', ', $categories),
                        $consent->page_url ?? 'N/A',
                        $consent->consented_at->format('Y-m-d H:i:s')
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}