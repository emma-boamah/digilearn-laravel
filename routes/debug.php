<?php

use Illuminate\Support\Facades\Route;

// Debug route to check IP detection - REMOVE IN PRODUCTION
Route::get('/debug/ip-info', function () {
    if (!app()->environment(['local', 'development', 'testing'])) {
        abort(404);
    }
    
    $request = request();
    
    $data = [
        'get_client_ip()' => get_client_ip(),
        'request->ip()' => $request->ip(),
        'request->ips()' => $request->ips(),
        'headers' => [
            'X-Forwarded-For' => $request->header('X-Forwarded-For'),
            'X-Real-IP' => $request->header('X-Real-IP'),
            'X-Client-IP' => $request->header('X-Client-IP'),
            'CF-Connecting-IP' => $request->header('CF-Connecting-IP'),
            'HTTP_X_FORWARDED_FOR' => $request->header('HTTP_X_FORWARDED_FOR'),
            'HTTP_CLIENT_IP' => $request->header('HTTP_CLIENT_IP'),
        ],
        'server_vars' => [
            'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null,
            'HTTP_X_FORWARDED_FOR' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
            'HTTP_CLIENT_IP' => $_SERVER['HTTP_CLIENT_IP'] ?? null,
            'HTTP_X_REAL_IP' => $_SERVER['HTTP_X_REAL_IP'] ?? null,
        ],
        'environment' => app()->environment(),
        'user_agent' => $request->userAgent(),
    ];
    
    return response()->json($data, 200, [], JSON_PRETTY_PRINT);
})->name('debug.ip-info');
