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

if (!function_exists('get_academic_year')) {
    /**
     * Get the current academic year (September to August cycle)
     * 
     * @return string
     */
    function get_academic_year(): string
    {
        $now = now();
        $year = $now->year;
        $month = $now->month;

        // If month is September (9) or later, it's the start of a new academic year
        if ($month >= 9) {
            $nextYear = $year + 1;
            return "{$year}-{$nextYear}";
        }

        // If before September, we are in the second half of the previous year's cycle
        $prevYear = $year - 1;
        return "{$prevYear}-{$year}";
    }
}