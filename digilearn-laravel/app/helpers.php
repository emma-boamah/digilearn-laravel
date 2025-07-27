<?php

use Illuminate\Http\Request;

if (!function_exists('get_client_ip')) {
    function get_client_ip(): string
    {
        $request = app(Request::class);
        
        // Get IP through trusted proxy
        $ip = $request->ip();
        
        // Validation: Must be IPv4 or IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            return $ip;
        }

        // Fallback to direct connection
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}