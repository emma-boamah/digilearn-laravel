<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return [
            'total_consents' => self::count(),
            'recent_consents' => self::recent(30)->count(),
            'unique_ips' => self::distinct('ip_address')->count(),
            'consent_types' => self::getConsentTypeBreakdown(),
        ];
    }

    /**
     * Get breakdown of consent types
     */
    private static function getConsentTypeBreakdown()
    {
        $consents = self::select('consent_data')->get();
        $breakdown = [
            'preference' => 0,
            'analytics' => 0,
            'consent' => 0,
        ];

        foreach ($consents as $consent) {
            $data = $consent->consent_data ?? [];
            foreach (['preference', 'analytics', 'consent'] as $type) {
                if (($data[$type] ?? false)) {
                    $breakdown[$type]++;
                }
            }
        }

        return $breakdown;
    }
}