<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\School;
use Illuminate\Support\Facades\View;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $appUrl = parse_url(config('app.url'), PHP_URL_HOST);
        
        // Fallback for local development if config doesn't have a scheme
        if (!$appUrl) {
            $appUrl = config('app.url');
        }
        
        // If the host is not the main app URL and not 'www.' + appUrl
        if ($host !== $appUrl && $host !== 'www.' . $appUrl) {
            // Extract the subdomain
            $subdomain = str_replace('.' . $appUrl, '', $host);
            
            // In local environment, if app.url is localhost, host might be ucc.localhost
            if ($appUrl === 'localhost' && str_ends_with($host, '.localhost')) {
                $subdomain = str_replace('.localhost', '', $host);
            }
            
            if ($subdomain && $subdomain !== 'www' && $subdomain !== $host) {
                $school = School::where('subdomain', $subdomain)->where('status', 'active')->first();
                
                if ($school) {
                    // Bind the tenant to the service container
                    app()->instance('tenant', $school);
                    
                    // Share the tenant with all views for dynamic branding
                    View::share('tenant', $school);
                } else {
                    // Invalid subdomain
                    abort(404, 'Organization not found.');
                }
            }
        }
        
        return $next($request);
    }
}
