<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to access this area.');
        }

        // Check if user is admin (you can modify this logic based on your needs)
        $user = Auth::user();
        
        // For now, we'll check if user email contains 'admin' or has an 'is_admin' field
        // In production, you should have a proper role/permission system
        $isAdmin = $user->is_admin ?? false;
        
        // Alternative: check by email domain or specific emails
        if (!$isAdmin && !in_array($user->email, ['admin@digilearn.com', 'administrator@digilearn.com','admin@shoutoutgh.com', 'contact@shoutoutgh.com'])) {
            // Log unauthorized admin access attempt
            Log::channel('security')->warning('unauthorized_admin_access_attempt', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ]);
            
            abort(403, 'Unauthorized access to admin area.');
        }

        // Log successful admin access
        Log::channel('security')->info('admin_access_granted', [
            'user_id' => $user->id,
            'email' => $user->email,
            'route' => $request->route()->getName(),
            'ip' => $request->ip(),
            'timestamp' => now()->toISOString()
        ]);

        return $next($request);
    }
}
