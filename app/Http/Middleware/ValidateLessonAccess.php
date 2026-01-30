<?php

namespace App\Http\Middleware;

use App\Models\Video;
use App\Services\SubscriptionAccessService;
use Closure;
use Illuminate\Http\Request;

class ValidateLessonAccess
{
    public function handle(Request $request, Closure $next)
    {
        $lessonId = $request->route('id');
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Skip validation for non-lesson routes
        if (!$lessonId || !$user) {
            return $next($request);
        }
        
        // Validate lesson exists and user has access
        $lesson = Video::find($lessonId);
        if (!$lesson) {
            return response()->json(['error' => 'Lesson not found'], 404);
        }
        
        if (!$this->canUserAccessLesson($user, $lesson)) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        // Add validated lesson to request for later use
        $request->merge(['validated_lesson' => $lesson]);
        
        return $next($request);
    }
    
    private function canUserAccessLesson($user, $lesson): bool
    {
        // Superuser bypass
        if ($user->is_superuser) {
            return true;
        }
        
        // Implement subscription-based access check
        return SubscriptionAccessService::canAccessGradeLevel($user, $lesson->grade_level);
    }
}