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
        $response = $next($request);

        if (Auth::check()) {
            $user = Auth::user();
            $userId = $user->id;
            $redisKey = "user:{$userId}:last_seen";
            $ttl = 300; // 5 minutes

            $now = Carbon::now()->timestamp;

            // Check if key already exists (to detect first activity in a while)
            $alreadyOnline = Redis::exists($redisKey);

            // Update key with new TTL
            Redis::setex($redisKey, $ttl, $now);

            // If they were not online, mark them as online and broadcast
            if (!$alreadyOnline) {
                Log::info("User {$userId} came online");
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
            }

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
     * Generate a human-readable description
     */
    private function generateDescription(string $method, string $path, $route, int $statusCode): string
    {
        $action = $this->getActionVerb($method);
        $resource = $this->extractResourceName($path, $route);

        $description = ucfirst($action) . ' ' . $resource;

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
            // Convert route name to readable format
            $name = str_replace(['.', '-'], ' ', $route->getName());
            return ucwords($name);
        }

        // Extract from path
        $segments = explode('/', trim($path, '/'));
        $resource = end($segments);

        // Handle common patterns
        if (is_numeric($resource)) {
            $resource = prev($segments) . ' item';
        }

        return str_replace(['_', '-'], ' ', $resource);
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
