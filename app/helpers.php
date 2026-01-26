<?php

use Illuminate\Http\Request;

// Polyfill for mb_split removed in PHP 8.2
if (!function_exists('mb_split')) {
    function mb_split($pattern, $string, $limit = -1) {
        return preg_split('/' . preg_quote($pattern, '/') . '/u', $string, $limit);
    }
}

if (!function_exists('get_client_ip')) {
    function get_client_ip(): string
    {
        $request = app(Request::class);
        
        // In development, just use request IP
        if (app()->environment(['local', 'development', 'testing'])) {
            return $request->ip() ?? '127.0.0.1';
        }
        
        // In production, check common headers but trust Laravel's request IP
        $headers = [
            'CF-Connecting-IP',    // Cloudflare
            'X-Real-IP',           // Nginx
            'X-Forwarded-For',     // Common proxy
        ];
        
        foreach ($headers as $header) {
            if ($ip = $request->header($header)) {
                // Handle comma-separated lists (take first IP)
                $ips = explode(',', $ip);
                $clientIp = trim($ips[0]);
                
                // Basic validation
                if (filter_var($clientIp, FILTER_VALIDATE_IP)) {
                    return $clientIp;
                }
            }
        }
        
        // Fallback to Laravel's IP
        return $request->ip() ?? '0.0.0.0';
    }
}