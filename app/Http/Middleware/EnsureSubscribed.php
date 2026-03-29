<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSubscribed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Bypass for superusers or users with active/trial subscriptions
        if ($user && ($user->is_superuser || $user->hasActiveSubscription() || $user->isInTrial())) {
            return $next($request);
        }

        // Handle JSON requests
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Subscription required',
                'message' => 'Please subscribe to a plan to access this resource.'
            ], 403);
        }

        // Redirect with message
        return redirect()->route('pricing')
            ->with('error', 'Please subscribe to a plan to access this dashboard feature.');
    }
}
