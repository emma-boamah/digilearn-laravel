<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Events\UserCameOnline;
use Carbon\Carbon;

class TrackUsersActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
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
                broadcast(new UserCameOnline($user))->toOthers();
            }
        }

        return $next($request);
    }
}
