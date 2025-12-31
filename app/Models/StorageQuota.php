<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class StorageQuota extends Model
{
    protected $fillable = [
        'quotable_type',
        'quotable_id',
        'quota_bytes',
        'used_bytes',
        'warning_threshold_percentage',
        'is_active'
    ];

    protected $casts = [
        'quota_bytes' => 'integer',
        'used_bytes' => 'integer',
        'warning_threshold_percentage' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Get the parent quotable model (User, Organization, etc.)
     */
    public function quotable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for active quotas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for quotas nearing limit
     */
    public function scopeNearingLimit($query, $threshold = 80)
    {
        return $query->whereRaw('(used_bytes / quota_bytes) * 100 >= ?', [$threshold]);
    }

    /**
     * Get usage percentage
     */
    protected function usagePercentage(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->quota_bytes > 0 ? round(($this->used_bytes / $this->quota_bytes) * 100, 2) : 0
        );
    }

    /**
     * Get remaining bytes
     */
    protected function remainingBytes(): Attribute
    {
        return Attribute::make(
            get: fn () => max(0, $this->quota_bytes - $this->used_bytes)
        );
    }

    /**
     * Get formatted quota
     */
    protected function formattedQuota(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->formatBytes($this->quota_bytes)
        );
    }

    /**
     * Get formatted used space
     */
    protected function formattedUsed(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->formatBytes($this->used_bytes)
        );
    }

    /**
     * Get formatted remaining space
     */
    protected function formattedRemaining(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->formatBytes($this->remaining_bytes)
        );
    }

    /**
     * Check if quota is exceeded
     */
    public function isExceeded(): bool
    {
        return $this->used_bytes >= $this->quota_bytes;
    }

    /**
     * Check if quota is nearing warning threshold
     */
    public function isNearingWarning(): bool
    {
        return $this->usage_percentage >= $this->warning_threshold_percentage;
    }

    /**
     * Add bytes to used space
     */
    public function addUsage(int $bytes): bool
    {
        if ($this->used_bytes + $bytes > $this->quota_bytes) {
            return false; // Would exceed quota
        }

        $this->used_bytes += $bytes;
        return $this->save();
    }

    /**
     * Remove bytes from used space
     */
    public function removeUsage(int $bytes): bool
    {
        $this->used_bytes = max(0, $this->used_bytes - $bytes);
        return $this->save();
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
     * Get quota status for dashboard
     */
    public function getStatus(): array
    {
        return [
            'id' => $this->id,
            'quotable_type' => $this->quotable_type,
            'quotable_name' => $this->quotable?->name ?? 'Unknown',
            'quota_bytes' => $this->quota_bytes,
            'used_bytes' => $this->used_bytes,
            'usage_percentage' => $this->usage_percentage,
            'is_exceeded' => $this->isExceeded(),
            'is_warning' => $this->isNearingWarning(),
            'formatted_quota' => $this->formatted_quota,
            'formatted_used' => $this->formatted_used,
            'formatted_remaining' => $this->formatted_remaining
        ];
    }
}
