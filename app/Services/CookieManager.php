<?php

namespace App\Services;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class CookieManager
{
    // Cookie types
    const TYPE_PREFERENCE = 'preference';
    const TYPE_ANALYTICS = 'analytics';
    const TYPE_CONSENT = 'consent';

    // Cookie categories
    const CATEGORIES = [
        self::TYPE_PREFERENCE => 'Essential and preference cookies',
        self::TYPE_ANALYTICS => 'Analytics and performance cookies',
        self::TYPE_CONSENT => 'Consent management cookies',
    ];

    // Default cookie settings
    const DEFAULT_SETTINGS = [
        self::TYPE_PREFERENCE => true,  // Always enabled
        self::TYPE_ANALYTICS => false,  // Requires consent
        self::TYPE_CONSENT => true,     // Always enabled
    ];

    /**
     * Check if a specific cookie type is allowed
     */
    public function isAllowed(string $type): bool
    {
        // Preferences are always allowed
        if ($type === self::TYPE_PREFERENCE) {
            return true;
        }

        // Check consent cookie
        $consent = $this->getConsent();
        return $consent[$type] ?? self::DEFAULT_SETTINGS[$type] ?? false;
    }

    /**
     * Get current consent settings
     */
    public function getConsent(): array
    {
        $consentCookie = Cookie::get('cookie_consent');
        if (!$consentCookie) {
            return self::DEFAULT_SETTINGS;
        }

        $consent = json_decode($consentCookie, true);
        return array_merge(self::DEFAULT_SETTINGS, $consent ?? []);
    }

    /**
     * Set consent for cookie types
     */
    public function setConsent(array $consent, array $gpsData = [], int $days = 365): void
    {
        $currentConsent = $this->getConsent();
        $newConsent = array_merge($currentConsent, $consent);

        Cookie::queue('cookie_consent', json_encode($newConsent), $days * 24 * 60);

        // Store consent data in database with GPS information
        $this->storeConsentRecord($newConsent, $gpsData);

        Log::info('Cookie consent updated', [
            'consent' => $newConsent,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'gps' => $gpsData
        ]);
    }

    /**
     * Accept all cookies
     */
    public function acceptAll(int $days = 365): void
    {
        $consent = [
            self::TYPE_PREFERENCE => true,
            self::TYPE_ANALYTICS => true,
            self::TYPE_CONSENT => true,
        ];

        $this->setConsent($consent, [], $days);
    }

    /**
     * Accept all cookies with GPS data
     */
    public function acceptAllWithGPS(array $gpsData = [], int $days = 365): void
    {
        $consent = [
            self::TYPE_PREFERENCE => true,
            self::TYPE_ANALYTICS => true,
            self::TYPE_CONSENT => true,
        ];

        $this->setConsent($consent, $gpsData, $days);
    }

    /**
     * Reject all non-essential cookies
     */
    public function rejectAll(int $days = 365): void
    {
        $consent = [
            self::TYPE_PREFERENCE => true,
            self::TYPE_ANALYTICS => false,
            self::TYPE_CONSENT => true,
        ];

        $this->setConsent($consent, [], $days);
    }

    /**
     * Reject all non-essential cookies with GPS data
     */
    public function rejectAllWithGPS(array $gpsData = [], int $days = 365): void
    {
        $consent = [
            self::TYPE_PREFERENCE => true,
            self::TYPE_ANALYTICS => false,
            self::TYPE_CONSENT => true,
        ];

        $this->setConsent($consent, $gpsData, $days);
    }

    /**
     * Set a preference cookie (always allowed)
     */
    public function setPreference(string $key, $value, int $minutes = 525600): void
    {
        if (!$this->isAllowed(self::TYPE_PREFERENCE)) {
            Log::warning('Attempted to set preference cookie when not allowed', ['key' => $key]);
            return;
        }

        Cookie::queue("pref_{$key}", $value, $minutes);
    }

    /**
     * Get a preference cookie
     */
    public function getPreference(string $key, $default = null)
    {
        return Cookie::get("pref_{$key}", $default);
    }

    /**
     * Set an analytics cookie
     */
    public function setAnalytics(string $key, $value, int $minutes = 525600): void
    {
        if (!$this->isAllowed(self::TYPE_ANALYTICS)) {
            Log::warning('Attempted to set analytics cookie when not allowed', ['key' => $key]);
            return;
        }

        Cookie::queue("analytics_{$key}", $value, $minutes);
    }

    /**
     * Get an analytics cookie
     */
    public function getAnalytics(string $key, $default = null)
    {
        if (!$this->isAllowed(self::TYPE_ANALYTICS)) {
            return $default;
        }

        return Cookie::get("analytics_{$key}", $default);
    }

    /**
     * Check if user has given consent
     */
    public function hasConsent(): bool
    {
        return Cookie::has('cookie_consent');
    }

    /**
     * Get cookie categories for frontend
     */
    public function getCategories(): array
    {
        return self::CATEGORIES;
    }

    /**
     * Get cookie statistics
     */
    public function getStats(): array
    {
        return [
            'has_consent' => $this->hasConsent(),
            'consent' => $this->getConsent(),
            'categories' => $this->getCategories(),
        ];
    }

    /**
     * Delete all non-essential cookies
     */
    public function deleteNonEssential(): void
    {
        // Get all cookies
        $cookies = Cookie::get();

        foreach ($cookies as $name => $value) {
            // Skip essential cookies
            if (in_array($name, ['cookie_consent', 'laravel_session', 'XSRF-TOKEN'])) {
                continue;
            }

            // Delete analytics cookies if not allowed
            if (str_starts_with($name, 'analytics_') && !$this->isAllowed(self::TYPE_ANALYTICS)) {
                Cookie::queue(Cookie::forget($name));
            }
        }
    }

    /**
     * Store consent record in database
     */
    private function storeConsentRecord(array $consent, array $gpsData = []): void
    {
        try {
            \App\Models\CookieConsent::create([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'consent_data' => $consent,
                'consent_hash' => md5(json_encode($consent)),
                'consented_at' => now(),
                'latitude' => $gpsData['latitude'] ?? null,
                'longitude' => $gpsData['longitude'] ?? null,
                'country' => $gpsData['country'] ?? null,
                'city' => $gpsData['city'] ?? null,
                'region' => $gpsData['region'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store cookie consent record', [
                'error' => $e->getMessage(),
                'consent' => $consent,
                'gps' => $gpsData
            ]);
        }
    }
}