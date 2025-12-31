<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class StorageAnalytic extends Model
{
    protected $fillable = [
        'path',
        'total_space_bytes',
        'used_space_bytes',
        'free_space_bytes',
        'usage_percentage',
        'growth_rate_percentage',
        'file_counts',
        'measured_at'
    ];

    protected $casts = [
        'total_space_bytes' => 'integer',
        'used_space_bytes' => 'integer',
        'free_space_bytes' => 'integer',
        'usage_percentage' => 'decimal:2',
        'growth_rate_percentage' => 'decimal:2',
        'file_counts' => 'array',
        'measured_at' => 'datetime'
    ];

    /**
     * Scope for recent analytics
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('measured_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope for specific path
     */
    public function scopeForPath($query, $path)
    {
        return $query->where('path', $path);
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
     * Get formatted used space
     */
    protected function formattedUsedSpace(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->formatBytes($this->used_space_bytes)
        );
    }

    /**
     * Get formatted free space
     */
    protected function formattedFreeSpace(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->formatBytes($this->free_space_bytes)
        );
    }

    /**
     * Calculate growth rate compared to previous measurement
     */
    public function calculateGrowthRate(): float
    {
        $previous = self::where('path', $this->path)
            ->where('measured_at', '<', $this->measured_at)
            ->latest('measured_at')
            ->first();

        if (!$previous) {
            return 0.0;
        }

        $timeDiffHours = $this->measured_at->diffInHours($previous->measured_at);
        if ($timeDiffHours == 0) {
            return 0.0;
        }

        $usageDiff = $this->usage_percentage - $previous->usage_percentage;
        return round($usageDiff / $timeDiffHours, 2);
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
     * Get analytics data for charts
     */
    public static function getChartData(string $path, int $days = 7): array
    {
        $data = self::forPath($path)
            ->where('measured_at', '>=', now()->subDays($days))
            ->orderBy('measured_at')
            ->get();

        return [
            'labels' => $data->pluck('measured_at')->map(fn($date) => $date->format('M d H:i')),
            'usage_percentages' => $data->pluck('usage_percentage'),
            'used_space' => $data->pluck('used_space_bytes'),
            'growth_rates' => $data->pluck('growth_rate_percentage')
        ];
    }
}
