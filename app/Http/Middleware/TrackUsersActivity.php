<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Events\UserCameOnline;
use App\Services\UserActivityService;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class TrackUsersActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $userId = $user->id;
            $redisKey = "user:{$userId}:last_seen";
            $ttl = 120; // 2 minutes (Strict real-time tracking)

            $now = Carbon::now()->timestamp;

            // Check if key already exists (to detect first activity in a while)
            $alreadyOnline = Redis::exists($redisKey);

            // Update key with new TTL
            Redis::setex($redisKey, $ttl, $now);

            // If they were not online, mark them as online and broadcast
            if (!$alreadyOnline) {
                // Sync to database
                $user->update([
                    'last_activity_at' => now(),
                    'is_online' => true
                ]);

                try {
                    broadcast(new UserCameOnline($user))->toOthers();
                } catch (\Exception $e) {
                    // Don't block request if broadcasting fails
                    Log::warning('Broadcasting failed (non-blocking)', [
                        'user_id' => $userId,
                        'error' => $e->getMessage(),
                        'path' => $request->path()
                    ]);
                }
            } elseif (!$user->last_activity_at || $user->last_activity_at->diffInMinutes(now()) >= 2) {
                // Throttled update of last_activity_at to keep it semi-accurate in DB (every 2 mins)
                $user->update(['last_activity_at' => now(), 'is_online' => true]);
            }
        }

        $response = $next($request);

        if (Auth::check()) {
            // Log user activity
            $this->logUserActivity($request, $response);
        }

        return $response;
    }

    /**
     * Log user activity based on the request
     */
    private function logUserActivity(Request $request, Response $response): void
    {
        try {
            $route = $request->route();
            $method = $request->method();
            $path = $request->path();

            // Skip logging for certain routes to avoid noise
            if ($this->shouldSkipLogging($path, $method)) {
                return;
            }

            $activityType = $this->determineActivityType($method, $path, $route);
            $description = $this->generateDescription($method, $path, $route, $response->getStatusCode());

            $metadata = [
                'method' => $method,
                'path' => $path,
                'status_code' => $response->getStatusCode(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'route_name' => $route ? $route->getName() : null,
                'route_parameters' => $route ? $route->parameters() : [],
            ];

            // Add additional metadata for specific actions
            $metadata = array_merge($metadata, $this->getAdditionalMetadata($request, $path));

            UserActivityService::logActivity(
                $activityType,
                $description,
                $metadata,
                Auth::id(),
                $request->ip(),
                $request->userAgent()
            );

        } catch (\Exception $e) {
            // Log the error but don't interrupt the response
            Log::error('Failed to track user activity', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'path' => $request->path(),
                'method' => $request->method()
            ]);
        }
    }

    /**
     * Determine if logging should be skipped for this request
     */
    private function shouldSkipLogging(string $path, string $method): bool
    {
        $skipPatterns = [
            'api/user-activities', // Avoid logging activity API calls
            '_debugbar', // Laravel Debugbar
            'admin/dashboard', // Dashboard views (too frequent)
            'favicon.ico',
            'css/',
            'js/',
            'images/',
            'storage/',
            'admin/contents/upload/video-chunk', // Skip chunked video uploads to avoid temp file issues
        ];

        // Skip background AJAX / polling requests that are not real user actions
        $skipAjaxPatterns = [
            'check-saved',       // Auto-fires on page load to check bookmark status
            'user-notes',        // Auto-loads saved notes
            '/progress',         // Auto-reports video progress
            'dashboard/stats',   // Dashboard stats polling
            'activity-stats',    // Activity stats API
            'user-activities',   // Activity feed API
            'security/data',     // Security data API
        ];

        foreach ($skipAjaxPatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }
        }

        foreach ($skipPatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }
        }

        // Skip GET requests for static assets
        if ($method === 'GET' && preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/i', $path)) {
            return true;
        }

        return false;
    }

    /**
     * Determine the activity type based on the request
     */
    private function determineActivityType(string $method, string $path, $route): string
    {
        // Check route name first
        if ($route && $route->getName()) {
            $routeName = $route->getName();

            if (str_contains($routeName, 'login')) return 'user_login';
            if (str_contains($routeName, 'logout')) return 'user_logout';
            if (str_contains($routeName, 'register')) return 'user_registration';
            if (str_contains($routeName, 'profile')) return 'profile_update';
            if (str_contains($routeName, 'password')) return 'password_change';
            if (str_contains($routeName, 'lesson')) return 'lesson_access';
            if (str_contains($routeName, 'video')) return 'video_access';
            if (str_contains($routeName, 'quiz')) return 'quiz_access';
            if (str_contains($routeName, 'payment')) return 'payment_action';
            if (str_contains($routeName, 'subscription')) return 'subscription_action';
        }

        // Fallback to path-based detection
        if (str_contains($path, 'login')) return 'user_login';
        if (str_contains($path, 'logout')) return 'user_logout';
        if (str_contains($path, 'register')) return 'user_registration';
        if (str_contains($path, 'profile')) return 'profile_update';
        if (str_contains($path, 'lesson')) return 'lesson_access';
        if (str_contains($path, 'video')) return 'video_access';
        if (str_contains($path, 'quiz')) return 'quiz_access';
        if (str_contains($path, 'payment')) return 'payment_action';

        // Generic activity types based on HTTP method
        switch ($method) {
            case 'POST':
                return 'data_creation';
            case 'PUT':
            case 'PATCH':
                return 'data_update';
            case 'DELETE':
                return 'data_deletion';
            case 'GET':
            default:
                return 'page_view';
        }
    }

    /**
     * Known route name → human-readable description mapping.
     * This avoids naive route-name-to-title conversion for common routes.
     */
    private const ROUTE_DESCRIPTIONS = [
        // Auth
        'login' => 'Logged in',
        'login.submit' => 'Logged in',
        'logout' => 'Logged out',
        'register' => 'Registered',
        'register.submit' => 'Registered',
        'password.request' => 'Requested password reset',
        'password.reset' => 'Reset password',

        // Dashboard & Lessons
        'dashboard' => 'Viewed dashboard',
        'dashboard.digilearn' => 'Viewed learning dashboard',
        'dashboard.lesson.view' => 'Viewed lesson',
        'dashboard.lesson.save' => 'Saved lesson to bookmarks',
        'dashboard.lesson.unsave' => 'Removed lesson from bookmarks',
        'dashboard.lesson.comment' => 'Commented on lesson',
        'dashboard.lesson.comment.store' => 'Posted a comment',
        'dashboard.saved-lessons' => 'Viewed saved lessons',
        'dashboard.lesson.document.pdf' => 'Downloaded lesson PDF',
        'dashboard.lesson.document.ppt' => 'Downloaded lesson presentation',

        // Profile
        'profile.index' => 'Viewed profile',
        'profile.update' => 'Updated profile',
        'profile.avatar.update' => 'Changed avatar',

        // Subscriptions & Payments
        'subscription.checkout' => 'Started subscription checkout',
        'payment.verify' => 'Verified payment',
        'plans.index' => 'Viewed pricing plans',

        // Admin
        'admin.dashboard' => 'Viewed admin dashboard',
        'admin.contents.index' => 'Viewed content library',
        'admin.contents.store' => 'Uploaded content',
        'admin.contents.update' => 'Updated content',
        'admin.contents.destroy' => 'Deleted content',
        'admin.users' => 'Viewed user management',
        'admin.users.show' => 'Viewed user details',
        'admin.tutors.index' => 'Viewed tutor applications',
        'admin.tutors.show' => 'Reviewed tutor application',
        'admin.tutors.approve' => 'Approved tutor application',
        'admin.tutors.reject' => 'Rejected tutor application',
        'admin.tutors.document' => 'Viewed tutor document',
        'admin.platform-settings.index' => 'Viewed platform settings',
        'admin.platform-settings.update' => 'Updated platform settings',

        // Tutors
        'tutors.apply' => 'Viewed tutor application form',
        'tutors.apply.submit' => 'Submitted tutor application',
        'tutors.dashboard' => 'Viewed tutor dashboard',
        'tutors.profile-settings' => 'Viewed tutor profile settings',
        'tutors.profile-settings.update' => 'Updated tutor profile settings',

        // Quizzes
        'quiz.index' => 'Viewed quizzes',
        'quiz.show' => 'Started quiz',
        'quiz.submit' => 'Submitted quiz answers',

        // Bookings
        'booking.checkout' => 'Started booking checkout',
        'booking.complete' => 'Completed booking',
    ];

    /**
     * Generate a human-readable description
     */
    private function generateDescription(string $method, string $path, $route, int $statusCode): string
    {
        // Check explicit mapping first
        if ($route && $route->getName() && isset(self::ROUTE_DESCRIPTIONS[$route->getName()])) {
            $description = self::ROUTE_DESCRIPTIONS[$route->getName()];
        } else {
            $action = $this->getActionVerb($method);
            $resource = $this->extractResourceName($path, $route);
            $description = ucfirst($action) . ' ' . $resource;
        }

        if ($statusCode >= 400) {
            $description .= ' (Failed - ' . $statusCode . ')';
        }

        return $description;
    }

    /**
     * Get action verb based on HTTP method
     */
    private function getActionVerb(string $method): string
    {
        return match ($method) {
            'POST' => 'created',
            'PUT', 'PATCH' => 'updated',
            'DELETE' => 'deleted',
            'GET' => 'viewed',
            default => 'accessed'
        };
    }

    /**
     * Extract resource name from path or route
     */
    private function extractResourceName(string $path, $route): string
    {
        if ($route && $route->getName()) {
            $routeName = $route->getName();

            // Remove common prefixes for cleaner output
            $routeName = preg_replace('/^(admin|dashboard|api)\./', '', $routeName);

            // Convert route name to readable format
            $name = str_replace(['.', '-', '_'], ' ', $routeName);
            return ucwords(trim($name));
        }

        // Extract from path — filter out obfuscated/encoded IDs and numeric segments
        $segments = explode('/', trim($path, '/'));
        $meaningful = array_filter($segments, function ($seg) {
            // Skip numeric IDs
            if (is_numeric($seg)) return false;
            // Skip base64/obfuscated tokens (very long alphanumeric strings)
            if (strlen($seg) > 40) return false;
            // Skip common prefixes already implied
            if (in_array($seg, ['api', 'v1', 'v2'])) return false;
            return true;
        });

        if (empty($meaningful)) {
            return 'page';
        }

        $resource = implode(' ', array_slice($meaningful, 0, 3));
        return ucwords(str_replace(['_', '-'], ' ', $resource));
    }

    /**
     * Get additional metadata for specific actions
     */
    private function getAdditionalMetadata(Request $request, string $path): array
    {
        $metadata = [];

        // Add lesson/video/quiz IDs if present
        if (preg_match('/\/lessons?\/(\d+)/', $path, $matches)) {
            $metadata['lesson_id'] = $matches[1];
        }

        if (preg_match('/\/videos?\/(\d+)/', $path, $matches)) {
            $metadata['video_id'] = $matches[1];
        }

        if (preg_match('/\/quizzes?\/(\d+)/', $path, $matches)) {
            $metadata['quiz_id'] = $matches[1];
        }

        // Add query parameters for search/filter actions
        if ($request->hasAny(['search', 'filter', 'q'])) {
            $metadata['search_query'] = $request->input('search') ?? $request->input('q') ?? $request->input('filter');
        }

        // Add form data for important actions (without sensitive data)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $safeFields = ['title', 'name', 'email', 'grade_level', 'subject_id', 'is_featured'];
            foreach ($safeFields as $field) {
                if ($request->has($field)) {
                    $metadata[$field] = $request->input($field);
                }
            }
        }

        return $metadata;
    }
}
