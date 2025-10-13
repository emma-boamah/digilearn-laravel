<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\WebsiteLockSetting;
use Illuminate\Support\Facades\Log;

class CheckWebsiteLock
{
    public function handle($request, Closure $next)
    {
        $lockSetting = WebsiteLockSetting::first();
        
        if ($lockSetting && $lockSetting->is_locked) {

            // Log authentication status
            Log::channel('security')->info('Lock status', [
                'is_locked' => true,
                'auth_check' => Auth::check(),
                'is_superuser' => Auth::check() ? Auth::user()->is_superuser : false,
                'path' => $request->path()
            ]);
            // Allow unlock routes
            if ($request->is('unlock*') || $request->is('admin/toggle-lock')) {
                return $next($request);
            }
            
            // Allow authenticated superusers
            if (Auth::check() && Auth::user()->is_superuser) {
                return $next($request);
            }
            
            return redirect()->route('unlock');
        }

        return $next($request);
    }
}
