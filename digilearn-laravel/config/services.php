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
        'enabled' => env('MAILBOXLAYER_ENABLED', false),
    ],

];
