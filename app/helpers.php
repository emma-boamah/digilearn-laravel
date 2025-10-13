<?php

use Illuminate\Http\Request;

if (!function_exists('get_client_ip')) {
    function get_client_ip(): string
    {
        $request = app(Request::class);
        
        // In development/Docker, we might want to allow private IPs
        $isDevelopment = app()->environment(['local', 'development', 'testing']);
        $ipFlags = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6;
        
        // Only exclude private/reserved ranges in production
        if (!$isDevelopment) {
            $ipFlags |= FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        }
        
        // Headers to check in order of preference
        $headers = [
            'X-Forwarded-For',     // Most common proxy header
            'CF-Connecting-IP',    // Cloudflare
            'X-Real-IP',           // Nginx proxy
            'X-Client-IP',         // Apache proxy
            'HTTP_X_FORWARDED_FOR', // Alternative format
            'HTTP_CLIENT_IP',      // Alternative format
            'HTTP_X_REAL_IP',      // Alternative format
        ];
        
        foreach ($headers as $header) {
            $headerValue = $request->header($header);
            if (!$headerValue) continue;
            
            // Handle comma-separated IPs (X-Forwarded-For can have multiple)
            $ips = is_array($headerValue) ? $headerValue : explode(',', $headerValue);
            
            foreach ($ips as $ip) {
                $ip = trim($ip);
                
                // Skip empty values
                if (empty($ip)) continue;
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, $ipFlags)) {
                    return $ip;
                }
            }
        }
        
        // Fallback to Laravel's request IP (handles most proxy scenarios)
        $requestIp = $request->ip();
        if ($requestIp && filter_var($requestIp, FILTER_VALIDATE_IP, $ipFlags)) {
            return $requestIp;
        }
        
        // Check server variables as last resort
        $serverIps = [
            $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
            $_SERVER['HTTP_CLIENT_IP'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ];
        
        foreach ($serverIps as $serverIp) {
            if (!$serverIp) continue;
            
            $ips = explode(',', $serverIp);
            foreach ($ips as $ip) {
                $ip = trim($ip);
                if ($ip && filter_var($ip, FILTER_VALIDATE_IP, $ipFlags)) {
                    return $ip;
                }
            }
        }
        
        // Ultimate fallback - return a recognizable development IP with context
        if ($isDevelopment) {
            // In development, include some context about the Docker environment
            $dockerIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            
            // If it's a Docker internal IP, add a prefix to make it clear
            if (str_starts_with($dockerIp, '172.') || str_starts_with($dockerIp, '10.')) {
                return "docker:{$dockerIp}";
            }
            
            return $dockerIp;
        }
        
        return '0.0.0.0';
    }
}