<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */

    'rate_limiting' => [
        'login_attempts' => env('THROTTLE_LOGIN_ATTEMPTS', 5),
        'login_decay_minutes' => env('THROTTLE_LOGIN_DECAY_MINUTES', 15),
        'signup_attempts' => env('THROTTLE_SIGNUP_ATTEMPTS', 3),
        'signup_decay_minutes' => env('THROTTLE_SIGNUP_DECAY_MINUTES', 60),
    ],

    'session' => [
        'cookie_name' => env('SESSION_COOKIE_NAME', 'laravel_session'),
        'cookie_secure' => env('SESSION_COOKIE_SECURE', false),
        'cookie_http_only' => env('SESSION_COOKIE_HTTP_ONLY', true),
        'cookie_same_site' => env('SESSION_COOKIE_SAME_SITE', 'lax'),
    ],

    'headers' => [
        'hsts_enabled' => env('HSTS_ENABLED', false),
        'hsts_max_age' => env('HSTS_MAX_AGE', 31536000),
        'hsts_include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
        'csp_enabled' => env('CSP_ENABLED', true),
    ],

    'authentication' => [
        'email_verification_required' => env('EMAIL_VERIFICATION_REQUIRED', false),
        'two_factor_enabled' => env('TWO_FACTOR_ENABLED', false),
        'password_reset_expire' => env('PASSWORD_RESET_EXPIRE', 60),
    ],

    'logging' => [
        'security_channel' => env('SECURITY_LOG_CHANNEL', 'security'),
        'security_level' => env('SECURITY_LOG_LEVEL', 'info'),
    ],
];