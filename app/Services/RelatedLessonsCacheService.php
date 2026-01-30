<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RelatedLessonsCacheService
{
    private const LOCK_TTL = 30; // seconds
    private const CACHE_TTL = 3600; // 1 hour
    
    public static function getRelatedLessons(string $cacheKey, callable $callback): array
    {
        $lockKey = $cacheKey . ':lock';
        
        // Try to acquire lock to prevent cache stampede
        if (Cache::add($lockKey, true, self::LOCK_TTL)) {
            try {
                $result = Cache::remember($cacheKey, self::CACHE_TTL, $callback);
                Cache::forget($lockKey);
                return $result;
            } catch (\Exception $e) {
                Cache::forget($lockKey);
                Log::error('Related lessons cache error', [
                    'cache_key' => $cacheKey,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
        
        // Lock exists, return stale data or fallback
        return Cache::get($cacheKey, function () use ($cacheKey) {
            Log::info('Cache lock acquired, returning stale data', [
                'cache_key' => $cacheKey
            ]);
            return []; // Emergency fallback
        });
    }
    
    public static function invalidateLessonCache(int $lessonId): void
    {
        // Pattern-based invalidation
        $pattern = "related_lessons:{$lessonId}:*";
        
        if (config('cache.default') === 'redis') {
            Cache::tags(['related_lessons'])->flush();
        } else {
            // For file cache, use known key patterns
            $keysToDelete = [
                "related_lessons:{$lessonId}:*"
            ];
            
            foreach ($keysToDelete as $keyPattern) {
                Cache::forget($keyPattern);
            }
        }
        
        Log::info('Related lessons cache invalidated', ['lesson_id' => $lessonId]);
    }
    
    public static function warmCacheForPopularLessons(): void
    {
        // Pre-warm cache for popular content
        try {
            $popularLessons = \App\Models\Video::approved()
                ->where('views', '>', 100)
                ->orderBy('views', 'desc')
                ->limit(20)
                ->get();
                
            $service = app(RelatedLessonsService::class);
            $user = \App\Models\User::where('is_superuser', true)->first(); // Use a system user or superuser for warming

            if (!$user) {
                Log::warning('No user found for cache warming');
                return;
            }

            foreach ($popularLessons as $lesson) {
                $service->getRelatedLessons($lesson->toArray(), $user);
                Log::debug('Cache warmed for popular lesson', ['lesson_id' => $lesson->id]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to warm cache for popular lessons', [
                'error' => $e->getMessage()
            ]);
        }
    }
}