<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TrackUsersActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Update only if last activity was more than 1 minute ago
            $user = Auth::user();

            // Ensure we have a Carbon instance
            $lastActivity = $user->last_activity_at ? now()->parse($user->last_activity_at) : null;

            // Check if update is needed
            $shouldUpdate = !$lastActivity || $lastActivity->lt(now()->subMinutes(1));

            Log::debug('TrackUserActivity: Authenticated user', [
                'user_id' => $user->id,
                'current_last_activity' => $lastActivity,
                'should_update' => $shouldUpdate
            ]);

            // Update last_activity_at if needed
            // This will only update if the last activity was more than 1 minute ago
            // This prevents unnecessary updates and database writes
            // You can adjust the time threshold as needed
            // For example, you might want to update every 5 minutes instead of 1 minute
            // if you expect users to be active for longer periods without refreshing.
            if ($shouldUpdate) {
                    Log::debug('Updating last_activity_at', ['user_id' => $user->id]);
                    $user->update(['last_activity_at' => now()]);
                }
            } else {
                Log::debug('TrackUserActivity: No authenticated user');}

            return $next($request);
        }
}
