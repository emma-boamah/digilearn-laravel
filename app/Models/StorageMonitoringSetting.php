<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class StorageMonitoringSetting extends Model
{
    protected $fillable = [
        'name',
        'description',
        'monitored_paths',
        'warning_threshold',
        'critical_threshold',
        'emergency_threshold',
        'recovery_threshold',
        'alert_throttle_minutes',
        'enable_predictive_alerts',
        'monitoring_interval_minutes',
        'auto_cleanup_enabled',
        'cleanup_threshold',
        'cleanup_rules',
        'is_active'
    ];

    protected $casts = [
        'monitored_paths' => 'array',
        'warning_threshold' => 'decimal:2',
        'critical_threshold' => 'decimal:2',
        'emergency_threshold' => 'decimal:2',
        'recovery_threshold' => 'decimal:2',
        'cleanup_threshold' => 'decimal:2',
        'cleanup_rules' => 'array',
        'is_active' => 'boolean',
        'enable_predictive_alerts' => 'boolean',
        'auto_cleanup_enabled' => 'boolean'
    ];

    /**
     * Get the active monitoring settings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the default settings
     */
    public function scopeDefault($query)
    {
        return $query->where('name', 'default');
    }

    /**
     * Get monitored paths as array
     */
    protected function monitoredPaths(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value)
        );
    }

    /**
     * Get cleanup rules as array
     */
    protected function cleanupRules(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? json_decode($value, true) : [],
            set: fn ($value) => json_encode($value)
        );
    }

    /**
     * Get alert level for a given percentage
     */
    public function getAlertLevel(float $percentage): ?string
    {
        if ($percentage >= $this->emergency_threshold) {
            return 'emergency';
        } elseif ($percentage >= $this->critical_threshold) {
            return 'critical';
        } elseif ($percentage >= $this->warning_threshold) {
            return 'warning';
        }

        return null;
    }

    /**
     * Check if alert should be throttled
     */
    public function shouldThrottleAlert(string $alertType, string $path): bool
    {
        $lastAlert = StorageAlert::where('alert_type', $alertType)
            ->where('path', $path)
            ->latest('alert_sent_at')
            ->first();

        if (!$lastAlert) {
            return false;
        }

        $minutesSinceLastAlert = $lastAlert->alert_sent_at->diffInMinutes(now());
        return $minutesSinceLastAlert < $this->alert_throttle_minutes;
    }
}
