<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RealIpMiddleware
{
    /**
     * Normalize client IP using common CDN/proxy headers so Request::ip() resolves the real user IP.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $candidateHeaders = [
            'CF-Connecting-IP',   // Cloudflare
            'True-Client-IP',     // Akamai / CloudFront
            'X-Real-IP',          // Nginx / reverse proxies
        ];

        foreach ($candidateHeaders as $header) {
            $value = $request->headers->get($header);
            if (!$value) {
                continue;
            }

            $ip = trim(is_array($value) ? reset($value) : $value);

            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
                // Set server REMOTE_ADDR for frameworks/components that use it
                $request->server->set('REMOTE_ADDR', $ip);

                // If X-Forwarded-For is empty, set it to the real client IP so TrustProxies can pick it up
                if (!$request->headers->has('X-Forwarded-For') || empty($request->headers->get('X-Forwarded-For'))) {
                    $request->headers->set('X-Forwarded-For', $ip);
                }

                break;
            }
        }

        return $next($request);
    }
}