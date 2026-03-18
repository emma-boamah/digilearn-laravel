<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CookieConsent extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'user_agent',
        'latitude',
        'longitude',
        'country',
        'city',
        'region',
        'consent_data',
        'consent_hash',
        'page_url',
        'consented_at',
    ];

    protected $casts = [
        'consent_data' => 'array',
        'consented_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Scope for recent consents
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('consented_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific IP
     */
    public function scopeByIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Get consent statistics
     */
    public static function getConsentStats()
    {
        $breakdown = self::getConsentTypeBreakdown();
        return array_merge([
            'total_consents' => self::count(),
            'recent_consents' => self::recent(30)->count(),
            'unique_ips' => self::distinct('ip_address')->count(),
        ], $breakdown);
    }

    /**
     * Get breakdown of consent types and total cookies
     */
    private static function getConsentTypeBreakdown()
    {
        $consents = self::select('consent_data')->get();
        $breakdown = [
            'preference' => 0,
            'analytics' => 0,
            'consent' => 0,
            'accepted_all' => 0,
            'rejected_all' => 0,
            'total_cookies' => 0, // Standard default cookies count as shown in image
        ];

        foreach ($consents as $consent) {
            $data = $consent->consent_data ?? [];

            $allAccepted = true;
            $noneOptionalAccepted = true;

            foreach (['preference', 'analytics', 'consent'] as $type) {
                if (($data[$type] ?? false)) {
                    $breakdown[$type]++;
                }
                else {
                    $allAccepted = false;
                    if ($type === 'analytics') {
                        $noneOptionalAccepted = true;
                    }
                }
            }

            if ($allAccepted) {
                $breakdown['accepted_all']++;
            }
            elseif (isset($data['analytics']) && $data['analytics'] === false) {
                $breakdown['rejected_all']++;
            }
        }

        // Add proxies for terms and privacy if they track with 'consent' category
        $breakdown['terms_accepted'] = $breakdown['consent'];
        $breakdown['privacy_accepted'] = $breakdown['consent'];

        return $breakdown;
    }

    /**
     * Get the device type from the user agent
     */


    /**
     * Get the browser name from the user agent
     */
    public function getBrowserAttribute()
    {
        $agent = $this->user_agent;
        if (empty($agent)) return 'Unknown';

        // Order matters: Check for specific "overlappers" first
        if (strpos($agent, 'OPR') !== false || strpos($agent, 'Opera') !== false) return 'Opera';
        if (strpos($agent, 'Edg') !== false) return 'Edge'; // Modern Edge uses 'Edg'
        if (strpos($agent, 'Chrome') !== false) return 'Chrome';
        if (strpos($agent, 'Safari') !== false) return 'Safari';
        if (strpos($agent, 'Firefox') !== false) return 'Firefox';
        if (strpos($agent, 'MSIE') !== false || strpos($agent, 'Trident') !== false) return 'IE';
        
        return 'Other';
    }

    /**
     * Get breakdown of consents by country
     */
    public static function getCountryBreakdown()
    {
        return self::select('country', DB::raw('count(*) as count'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->pluck('count', 'country')
            ->toArray();
    }
}