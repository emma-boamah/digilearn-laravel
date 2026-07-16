<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyB2BSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $school = app()->has('tenant') ? app('tenant') : null;

        // If it's a B2B request and the school exists
        if ($school) {
            $user = \Illuminate\Support\Facades\Auth::user();
            
            // Allow superusers to bypass subscription checks completely
            if ($user && $user->is_superuser) {
                return $next($request);
            }

            // If the subscription is totally expired (past grace period)
            if (!$school->hasActiveSubscription() && !$school->isInGracePeriod()) {
                // Allow access to essential routes: billing, auth routes, logout, etc.
                if ($request->is('school-admin/billing*') || 
                    $request->is('payment*') ||
                    $request->is('logout') || 
                    $request->is('login') || 
                    $request->routeIs('login') || 
                    $request->routeIs('logout')) {
                    return $next($request);
                }

                // If not trying to access billing, redirect to a subscription expired page
                // Or if it's a student/teacher, they just see an error
                if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->hasRole('school-admin')) {
                    return redirect()->route('school.admin.billing')
                        ->with('error', 'Your school subscription has expired. Please renew to restore access.');
                }

                abort(403, 'The subscription for this institution has expired. Please contact your school administrator.');
            }
        }

        return $next($request);
    }
}
