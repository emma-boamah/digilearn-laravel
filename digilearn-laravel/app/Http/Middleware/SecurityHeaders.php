<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Config;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Generate nonce for CSP
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);

        $response = $next($request);

        // Apply security headers only to HTML responses
        if ($this->isHtmlResponse($response)) {
            $this->applySecurityHeaders($request, $response, $nonce);
        }

        return $response;
    }

    protected function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type') ?? '';
        return str_contains($contentType, 'text/html') || 
               str_contains($contentType, 'application/xhtml+xml');
    }

    protected function applySecurityHeaders(
        Request $request, 
        Response $response, 
        string $nonce
    ): void {
        // Set standard security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // Remove server information
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');

        // HSTS Header
        if ($request->isSecure() && Config::get('security.headers.hsts_enabled', true)) {
            $this->applyHstsHeader($response);
        }

        // Content Security Policy
        if (Config::get('security.headers.csp_enabled', true)) {
            $csp = $this->buildContentSecurityPolicy($request, $nonce);
            
            // Check if report-only mode is enabled
            if (Config::get('csp.report_only', false)) {
                $response->headers->set('Content-Security-Policy-Report-Only', $csp);
            } else {
                $response->headers->set('Content-Security-Policy', $csp);
            }
        }
    }

    protected function applyHstsHeader(Response $response): void
    {
        $maxAge = Config::get('security.headers.hsts_max_age', 31536000);
        $includeSubdomains = Config::get('security.headers.hsts_include_subdomains', true) 
            ? '; includeSubDomains' : '';
        $preload = Config::get('security.headers.hsts_preload', false) 
            ? '; preload' : '';
        
        $response->headers->set(
            'Strict-Transport-Security', 
            "max-age={$maxAge}{$includeSubdomains}{$preload}"
        );
    }

    protected function buildContentSecurityPolicy(
        Request $request, 
        string $nonce
    ): string {
        $config = Config::get('csp', []);
        $policies = [];
        
        // Build CSP directives from config
        foreach ($config['directives'] ?? [] as $directive => $sources) {
            // Handle nonce replacement
            $processedSources = array_map(function ($source) use ($nonce) {
                return str_replace('{nonce}', $nonce, $source);
            }, $sources);
            
            $policies[] = $directive . ' ' . implode(' ', $processedSources);
        }
        
        // Add report URI if configured
        if ($reportUri = $config['report_uri'] ?? null) {
            $policies[] = "report-uri $reportUri";
            $policies[] = "report-to default";
        }

        // Return minimal safe CSP if no policies are defined
        if (empty($policies)) {
            return "default-src 'self'; script-src 'self' 'unsafe-inline' https://accounts.google.com; " .
                "frame-src 'self' https://accounts.google.com; " .
                "connect-src 'self' https://accounts.google.com https://www.googleapis.com";
        }
        
        return implode('; ', $policies);
    }
}