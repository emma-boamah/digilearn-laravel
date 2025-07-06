<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate the nonce ONCE per request
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);

        $response = $next($request);

        // Security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Remove server information
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');

        // HSTS Header (only for HTTPS)
        if ($request->isSecure() && config('security.headers.hsts_enabled', true)) {
            $maxAge = config('security.headers.hsts_max_age', 31536000);
            $includeSubdomains = config('security.headers.hsts_include_subdomains', true) ? '; includeSubDomains' : '';
            $preload = '; preload';
            $response->headers->set('Strict-Transport-Security', "max-age={$maxAge}{$includeSubdomains}{$preload}");
        }

        // Content Security Policy
        if (config('security.headers.csp_enabled', true)) {
            $csp = $this->buildContentSecurityPolicy($request, $nonce);
            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }

    /**
     * Build Content Security Policy header
     */
    private function buildContentSecurityPolicy(Request $request, string $nonce): string
    {
        // Define common script sources for reusability
        $scriptSources = [
            "'self'",
            "'nonce-{$nonce}'",
            'https://apis.google.com',
            'https://connect.facebook.net',
            'https://www.google.com',
            'https://www.gstatic.com',
            'https://cdn.quilljs.com',
            'https://cdnjs.cloudflare.com',
            'https://cdn.jsdelivr.net',
            'https://flagcdn.com'
        ];
        $scriptSrcString = implode(' ', $scriptSources);

        // Get base domain for form-action
        $appUrl = config('app.url');
        $host = parse_url($appUrl, PHP_URL_HOST);
        $domain = preg_replace('/^www\./', '', $host);

        $policies = [
            "default-src 'self'",
            // Script policies: 
            // - script-src for older browsers (fallback)
            // - script-src-elem for modern browsers (script elements)
            // - script-src-attr for inline event handlers
            "script-src {$scriptSrcString}",
            "script-src-elem {$scriptSrcString}",
            "script-src-attr 'unsafe-inline'",  // Allow inline event handlers
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://cdn.quilljs.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://flagcdn.com",
            "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net https://cdnjs.cloudflare.com",
            "img-src 'self' data: https: blob:",
            "media-src 'self' https: data: blob:",
            "connect-src 'self' https://api." . parse_url(config('app.url'), PHP_URL_HOST),
            "frame-src 'self' https://www.google.com https://www.facebook.com",
            "frame-ancestors 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action *;",  // Allow form submissions to any URL
            "upgrade-insecure-requests",
        ];

        // Add report-uri in production
        if (app()->environment('production')) {
            $policies[] = "report-uri /csp-report";
        }

        return implode('; ', $policies);
    }
}