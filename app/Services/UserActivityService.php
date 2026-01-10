<?php

namespace App\Services;

use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class UserActivityService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const SUSPICIOUS_IP_THRESHOLD = 2;
    private const SUSPICIOUS_TOTAL_THRESHOLD = 5;
    private const RAPID_ACTIVITY_THRESHOLD = 20;

    public static function logActivity(
        string $type,
        string $description,
        ?array $metadata = null,
        ?int $userId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): ?UserActivity {
        try {
            $userId = $userId ?? Auth::id();
            $ipAddress = $ipAddress ?? request()->ip();
            $userAgent = $userAgent ?? request()->userAgent();

            // Sanitize inputs
            $description = trim($description);
            $ipAddress = filter_var($ipAddress, FILTER_VALIDATE_IP) ? $ipAddress : null;

            $activity = UserActivity::create([
                'user_id' => $userId,
                'type' => $type,
                'description' => $description,
                'metadata' => $metadata,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            // Clear relevant caches
            self::clearActivityCaches($userId, $type);

            return $activity;
        } catch (\Exception $e) {
            // Log error but don't throw to avoid breaking user flow
            Log::error('Failed to log user activity', [
                'type' => $type,
                'description' => $description,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            // Return null on failure
            return null;
        }
    }

    public static function getRecentActivities(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "user_activities_recent_{$limit}";

        return Cache::tags(['activities'])->remember($cacheKey, self::CACHE_TTL, function () use ($limit) {
            return UserActivity::recent($limit)->get();
        });
    }

    public static function getActivitiesByUser(int $userId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "user_activities_user_{$userId}_{$limit}";

        return Cache::tags(['activities'])->remember($cacheKey, self::CACHE_TTL, function () use ($userId, $limit) {
            return UserActivity::byUser($userId)->latest()->limit($limit)->get();
        });
    }

    public static function getActivitiesByType(string $type, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "user_activities_type_{$type}_{$limit}";

        return Cache::tags(['activities'])->remember($cacheKey, self::CACHE_TTL, function () use ($type, $limit) {
            return UserActivity::byType($type)->latest()->limit($limit)->get();
        });
    }

    /**
     * Get paginated activities with filtering options
     */
    public static function getPaginatedActivities(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = UserActivity::with('user:id,name,email')->latest();

        // Apply filters
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['ip_address'])) {
            $query->where('ip_address', $filters['ip_address']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQ) use ($search) {
                      $userQ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get activity statistics
     */
    public static function getActivityStats(?int $userId = null, ?int $days = 30): array
    {
        $cacheKey = "activity_stats_" . ($userId ?? 'all') . "_{$days}";

        return Cache::tags(['activities'])->remember($cacheKey, self::CACHE_TTL, function () use ($userId, $days) {
            $baseQuery = UserActivity::query();

            if ($userId) {
                $baseQuery->where('user_id', $userId);
            }

            $baseQuery->where('created_at', '>=', now()->subDays($days));

            $totalActivities = (clone $baseQuery)->count();
            $uniqueUsers = (clone $baseQuery)->distinct('user_id')->count('user_id');

            $activitiesByType = (clone $baseQuery)->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'type')
                ->toArray();

            $activitiesByDay = (clone $baseQuery)->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('count', 'date')
                ->toArray();

            return [
                'total_activities' => $totalActivities,
                'unique_users' => $uniqueUsers,
                'activities_by_type' => $activitiesByType,
                'activities_by_day' => $activitiesByDay,
                'period_days' => $days,
            ];
        });
    }

    /**
     * Get suspicious activities (for security monitoring)
     */
    public static function getSuspiciousActivities(int $limit = 50): Collection
    {
        $cacheKey = "suspicious_activities_{$limit}";

        return Cache::tags(['activities'])->remember($cacheKey, self::CACHE_TTL, function () use ($limit) {
            $suspicious = collect();

            // Multiple IPs for same user in short time
            $multiIpActivities = UserActivity::where('created_at', '>=', now()->subHours(24))
                ->selectRaw('user_id, COUNT(DISTINCT ip_address) as ip_count, COUNT(*) as total_count')
                ->groupBy('user_id')
                ->having('ip_count', '>', self::SUSPICIOUS_IP_THRESHOLD)
                ->having('total_count', '>', self::SUSPICIOUS_TOTAL_THRESHOLD)
                ->with('user')
                ->get();

            foreach ($multiIpActivities as $activity) {
                $suspicious->push([
                    'type' => 'multiple_ips',
                    'user' => $activity->user,
                    'description' => "Multiple IP addresses ({$activity->ip_count}) used in 24 hours",
                    'severity' => 'medium',
                    'activity_count' => $activity->total_count,
                ]);
            }

            // Rapid activities from same IP
            $rapidActivities = UserActivity::where('created_at', '>=', now()->subHours(1))
                ->selectRaw('ip_address, COUNT(*) as count')
                ->groupBy('ip_address')
                ->having('count', '>', self::RAPID_ACTIVITY_THRESHOLD)
                ->get();

            foreach ($rapidActivities as $activity) {
                $suspicious->push([
                    'type' => 'rapid_activity',
                    'ip_address' => $activity->ip_address,
                    'description' => "{$activity->count} activities in 1 hour from single IP",
                    'severity' => 'high',
                    'activity_count' => $activity->count,
                ]);
            }

            return $suspicious->take($limit);
        });
    }

    /**
     * Clear activity caches when new activity is logged
     */
    private static function clearActivityCaches(?int $userId, ?string $type): void
    {
        Cache::tags(['activities'])->flush();
    }

    /**
     * Clean up old activities (for maintenance)
     */
    public static function cleanupOldActivities(int $daysOld = 365): int
    {
        return UserActivity::where('created_at', '<', now()->subDays($daysOld))->delete();
    }
}