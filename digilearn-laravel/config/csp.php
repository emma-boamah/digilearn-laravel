<?php

return [
    'report_uri' => env('CSP_REPORT_URI', '/csp-reports'),
    'report_group' => 'default',
    'upgrade_insecure_requests' => env('CSP_UPGRADE_INSECURE', false),
    
    'directives' => [
        'default-src' => ["'self'"],
        'script-src' => [
            "'self'",
            "'nonce-{nonce}'", // Nonce placeholder
            'https://www.google.com',
            'https://accounts.google.com',
            'https://www.gstatic.com',
            'https://apis.google.com',
            'https://accounts.google.com',
            'https://cdn.jsdelivr.net',
            'https://cdn.quilljs.com',
            'https://cdn.tailwindcss.com',
            'https://' . parse_url(env('APP_URL'), PHP_URL_HOST),
        ],
        'style-src' => [
            "'self'",
            "'unsafe-inline'", // Required for Google buttons
            'https://fonts.googleapis.com',
            'https://fonts.bunny.net',
            'https://cdn.jsdelivr.net',
            'https://' . parse_url(env('APP_URL'), PHP_URL_HOST),
            'https://cdnjs.cloudflare.com',

        ],
        'font-src' => [
            "'self'",
            'data:',
            'https://fonts.gstatic.com',
            'https://fonts.bunny.net',
            'https://cdnjs.cloudflare.com',
            'https://' . parse_url(env('APP_URL'), PHP_URL_HOST),
        ],
        'img-src' => [
            "'self'",
            'data:',
            'blob:',
            'https://*.googleusercontent.com',
            'https://images.unsplash.com',
            'https://flagcdn.com',
            'https://' . parse_url(env('APP_URL'), PHP_URL_HOST),
            
        ],
        'media-src' => [
            "'self'",
            'blob:',
            'https://' . parse_url(env('APP_URL'), PHP_URL_HOST),
        ],
        'connect-src' => [
            "'self'",
            'https://api.' . parse_url(env('APP_URL'), PHP_URL_HOST),
            'https://' . parse_url(env('APP_URL'), PHP_URL_HOST),
            'https://accounts.google.com',
            'https://oauth2.googleapis.com',
            'https://www.googleapis.com',
        ],
        'frame-src' => [
            "'self'",
            'https://accounts.google.com',
            'https://www.google.com',
        ],
        'object-src' => ["'none'"],
        'base-uri' => ["'self'"],
        'form-action' => [
            "'self'",
            'https://accounts.google.com'
        ],
        'frame-ancestors' => ["'none'"],
        'worker-src' => ["'self'", 'blob:'],
    ],
];