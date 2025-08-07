<?php

use Illuminate\Http\Request;

if (!function_exists('get_client_ip')) {
    function get_client_ip(): string
    {
        $request = app(Request::class);
        
        // First check the X-Forwarded-For header
        $ips = $request->header('X-Forwarded-For');
        if ($ips) {
            $ips = is_array($ips) ? $ips : explode(',', $ips);
            foreach ($ips as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        // Then check other headers in order of reliability
        $headers = [
            'CF-Connecting-IP', // Cloudflare
            'X-Real-IP',        // Nginx
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR'
        ];
        
        foreach ($headers as $header) {
            if ($request->hasHeader($header)) {
                $ip = $request->header($header);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        // Final fallback to request IP
        $ip = $request->ip();
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            return $ip;
        }

        // Ultimate fallback
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}