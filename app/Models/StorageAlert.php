<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class StorageAlert extends Model
{
    protected $fillable = [
        'alert_type',
        'path',
        'usage_percentage',
        'used_space_bytes',
        'total_space_bytes',
        'admin_users_notified',
        'alert_sent_at'
    ];

    protected $casts = [
        'usage_percentage' => 'decimal:2',
        'used_space_bytes' => 'integer',
        'total_space_bytes' => 'integer',
        'admin_users_notified' => 'array',
        'alert_sent_at' => 'datetime'
    ];

    /**
     * Scope for specific alert type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Scope for specific path
     */
    public function scopeForPath($query, $path)
    {
        return $query->where('path', $path);
    }

    /**
     * Scope for recent alerts
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('alert_sent_at', '>=', now()->subHours($hours));
    }

    /**
     * Get formatted used space
     */
    protected function formattedUsedSpace(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->formatBytes($this->used_space_bytes)
        );
    }

    /**
     * Get formatted total space
     */
    protected function formattedTotalSpace(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->formatBytes($this->total_space_bytes)
        );
    }

    /**
     * Get alert level color
     */
    protected function alertColor(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->alert_type) {
                    'emergency' => '#dc2626', // red-600
                    'critical' => '#ea580c',  // orange-600
                    'warning' => '#ca8a04',   // yellow-600
                    'recovery' => '#16a34a',  // green-600
                    default => '#6b7280'      // gray-500
                };
            }
        );
    }

    /**
     * Get alert level icon
     */
    protected function alertIcon(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->alert_type) {
                    'emergency' => 'fas fa-exclamation-triangle',
                    'critical' => 'fas fa-exclamation-circle',
                    'warning' => 'fas fa-exclamation-triangle',
                    'recovery' => 'fas fa-check-circle',
                    default => 'fas fa-info-circle'
                };
            }
        );
    }

    /**
     * Get alert level priority
     */
    protected function alertPriority(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->alert_type) {
                    'emergency' => 4,
                    'critical' => 3,
                    'warning' => 2,
                    'recovery' => 1,
                    default => 0
                };
            }
        );
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Get alert summary for dashboard
     */
    public static function getAlertSummary(): array
    {
        $last24Hours = now()->subHours(24);

        return [
            'total_alerts' => self::where('alert_sent_at', '>=', $last24Hours)->count(),
            'emergency_alerts' => self::ofType('emergency')->where('alert_sent_at', '>=', $last24Hours)->count(),
            'critical_alerts' => self::ofType('critical')->where('alert_sent_at', '>=', $last24Hours)->count(),
            'warning_alerts' => self::ofType('warning')->where('alert_sent_at', '>=', $last24Hours)->count(),
            'recovery_alerts' => self::ofType('recovery')->where('alert_sent_at', '>=', $last24Hours)->count(),
            'last_alert' => self::latest('alert_sent_at')->first()
        ];
    }
}
