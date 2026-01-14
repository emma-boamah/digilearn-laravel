<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleJsonRequestErrors
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
        // If this is an AJAX or API request expecting JSON, set the header
        if ($request->isJson() || $request->expectsJson() || $request->is('api/*') || $request->is('*/upload/*')) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
