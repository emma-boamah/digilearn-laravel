<?php

return [
    'report_uri' => env('CSP_REPORT_URI', '/csp-report'),
    'report_group' => 'default',
    'upgrade_insecure_requests' => env('CSP_UPGRADE_INSECURE', false),
    
    // Enable report-only mode for testing (set CSP_REPORT_ONLY=true in .env)
    'report_only' => env('CSP_REPORT_ONLY', false),
    
    'directives' => [
        'default-src' => ["'self'"],
        'script-src' => [
            "'self'",
            "'nonce-{nonce}'", // Nonce placeholder for inline scripts
            'https://www.google.com',
            'https://accounts.google.com',
            'https://www.gstatic.com',
            'https://apis.google.com',
            'https://cdn.jsdelivr.net',
            'https://cdn.jsdelivr.net/npm/chart.umd.min.js.map',
            'https://cdn.jsdelivr.net/npm/alpinejs',
            'https://cdn.quilljs.com',
            'https://schema.org',
            'https://cdn.tailwindcss.com',
            'https://' . parse_url(env('APP_URL'), PHP_URL_HOST),
            // Removed 'unsafe-inline' for better security - using nonces instead
            // Removeed 'unsafe-eval' not needed for Chart.js or other libraries now
            // "'unsafe-eval'", // Only enable if absolutely required
        ],
        'style-src' => [
            "'self'",
            "'nonce-{nonce}'", // Nonce placeholder for inline styles
            "'unsafe-inline'", // Keep temporarily for external libraries that inject styles
            'https://fonts.googleapis.com',
            'https://fonts.bunny.net',
            'https://cdn.jsdelivr.net',
            'https://' . parse_url(env('APP_URL'), PHP_URL_HOST),
            'https://cdnjs.cloudflare.com',
            'https://cdn.quilljs.com',
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
            'https://via.placeholder.com',
            'https://flagcdn.com',
            'https://www.shoutoutgh.com',
            'https://ui-avatars.com', // For user avatars
            'https://' . parse_url(env('APP_URL'), PHP_URL_HOST),
            'https://shoutoutgh.com',
        ],
        'media-src' => [
            "'self'",
            'blob:',
            'data:',
            'https://' . parse_url(env('APP_URL'), PHP_URL_HOST),
            'https://stream.mux.com',
            'https://*.mux.com',
        ],
        'connect-src' => [
            "'self'",
            'https://api.' . parse_url(env('APP_URL'), PHP_URL_HOST),
            'https://' . parse_url(env('APP_URL'), PHP_URL_HOST),
            'https://accounts.google.com',
            'https://oauth2.googleapis.com',
            'https://www.googleapis.com',
            'https://cdn.jsdelivr.net',
            'https://cdn.jsdelivr.net/npm/chart.umd.min.js.map',
            'https://cdn.jsdelivr.net/npm/alpinejs',
            'https://cdn.quilljs.com',
            'https://cdn.tailwindcss.com',
            'https://cdnjs.cloudflare.com',
            'https://via.placeholder.com',
            'https://api.vimeo.com',
            'https://vimeo.com',
            'https://ipapi.co',
            'https://schema.org',
    	 ],
        'frame-src' => [
            "'self'",
            'https://accounts.google.com',
            'https://www.google.com',
            'https://vimeo.com',
            'https://player.vimeo.com',
            'https://www.youtube.com',
            'https://youtube.com',
            'https://youtu.be',
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
