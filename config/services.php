<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'headers' => [
        'hsts_enabled' => env('HSTS_ENABLED', true),
        'hsts_max_age' => env('HSTS_MAX_AGE', 31536000), // 1 year
        'hsts_include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
        'hsts_preload' => env('HSTS_PRELOAD', false),
        'csp_enabled' => env('CSP_ENABLED', true),
        'csp_report_only' => env('CSP_REPORT_ONLY', false),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'security' => [
        // Whitelisted IP ranges (schools/businesses)
        'whitelisted_ips' => explode(',', env('RATE_LIMIT_WHITELISTED_IPS', '')),
        
        // Standard login limits
        'throttle_login_ip_attempts' => env('THROTTLE_LOGIN_IP_ATTEMPTS', 5),
        'throttle_login_email_attempts' => env('THROTTLE_LOGIN_EMAIL_ATTEMPTS', 3),
        'throttle_login_decay_minutes' => env('THROTTLE_LOGIN_DECAY_MINUTES', 15),
        
        // Whitelisted login limits
        'whitelist_login_ip_attempts' => env('WHITELIST_LOGIN_IP_ATTEMPTS', 50),
        'whitelist_login_email_attempts' => env('WHITELIST_LOGIN_EMAIL_ATTEMPTS', 20),
        'whitelist_login_decay_minutes' => env('WHITELIST_LOGIN_DECAY_MINUTES', 1),
        
        // Standard signup limits
        'throttle_signup_ip_attempts' => env('THROTTLE_SIGNUP_IP_ATTEMPTS', 10),
        'throttle_signup_email_attempts' => env('THROTTLE_SIGNUP_EMAIL_ATTEMPTS', 3),
        'throttle_signup_decay_minutes' => env('THROTTLE_SIGNUP_DECAY_MINUTES', 60),
        
        // Whitelisted signup limits
        'whitelist_signup_ip_attempts' => env('WHITELIST_SIGNUP_IP_ATTEMPTS', 100),
        'whitelist_signup_email_attempts' => env('WHITELIST_SIGNUP_EMAIL_ATTEMPTS', 20),
        'whitelist_signup_decay_minutes' => env('WHITELIST_SIGNUP_DECAY_MINUTES', 1),
    ],

    'mailboxlayer' => [
        'key' => env('MAILBOXLAYER_API_KEY'),
        'enabled' => env('MAILBOXLAYER_ENABLED', true),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
        'recaptcha_enabled' => env('GOOGLE_RECAPTCHA_ENABLED', false),
        'recaptcha_secret' => env('GOOGLE_RECAPTCHA_SECRET'),
        'rate_limit' => env('GOOGLE_RATE_LIMIT', 5),
    ],

    'vimeo' => [
        'access_token' => env('VIMEO_ACCESS_TOKEN'),
        'client_id' => env('VIMEO_CLIENT_ID'),
        'client_secret' => env('VIMEO_CLIENT_SECRET'),
        'max_temp_videos' => env('VIMEO_MAX_TEMP_VIDEOS', 10),
        'temp_expiry_hours' => env('VIMEO_TEMP_EXPIRY_HOURS', 72), // 3 days
    ],

    'youtube' => [
        'api_key' => env('YOUTUBE_API_KEY'),
    ],

    'mux' => [
        'token_id' => env('MUX_TOKEN_ID'),
        'token_secret' => env('MUX_TOKEN_SECRET'),
        'webhook_secret' => env('MUX_WEBHOOK_SECRET'),
        'max_temp_videos' => env('MUX_MAX_TEMP_VIDEOS', 10),
        'temp_expiry_hours' => env('MUX_TEMP_EXPIRY_HOURS', 72), // 3 days
    ],

    'paystack' => [
        'secret' => env('PAYSTACK_SECRET_KEY'),
        'public' => env('PAYSTACK_PUBLIC_KEY'),
        'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
    ],

];
