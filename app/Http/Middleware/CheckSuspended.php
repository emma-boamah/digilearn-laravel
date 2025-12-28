<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckSuspended
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isSuspended()) {
            // Log the attempt to access protected route while suspended
            Log::channel('security')->warning('suspended_user_access_attempt', [
                'user_id' => Auth::id(),
                'user_email' => Auth::user()->email,
                'suspended_at' => Auth::user()->suspended_at,
                'suspension_reason' => Auth::user()->suspension_reason,
                'route' => $request->route() ? $request->route()->getName() : 'unknown',
                'url' => $request->fullUrl(),
                'ip' => get_client_ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ]);

            // Logout the user
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Redirect to login with error message
            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been suspended. Please contact support for assistance.'
            ]);
        }

        return $next($request);
    }
}