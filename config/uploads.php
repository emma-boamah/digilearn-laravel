<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configure file upload limits for videos, documents, and quizzes
    | These values are in bytes and should match your server configuration
    |
    */

    'max_file_size' => env('MAX_UPLOAD_SIZE', 34359738368), // 32GB in bytes (34359738368)

    'video' => [
        'max_size' => env('VIDEO_MAX_SIZE', 34359738368), // 32GB in bytes
        'max_size_mb' => env('VIDEO_MAX_SIZE_MB', 32768), // 32GB in MB for display
        'max_size_display' => '32GB', // User-friendly display text
        'mimes' => ['mp4', 'mov', 'avi', 'mkv', 'webm', '3gp', 'mpeg', 'ogg', 'flv', 'wmv'],
        'allowed_mime_types' => [
            'video/mp4',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-matroska',
            'video/webm',
            'video/3gpp',
            'video/mpeg',
            'video/ogg',
            'video/x-flv',
            'video/x-ms-wmv',
        ],
    ],

    'document' => [
        'max_size' => env('DOCUMENT_MAX_SIZE', 34359738368), // 32GB in bytes
        'max_size_mb' => env('DOCUMENT_MAX_SIZE_MB', 32768), // 32GB in MB for display
        'max_size_display' => '32GB', // User-friendly display text
        'mimes' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'],
        'allowed_mime_types' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'application/rtf',
        ],
    ],

    'quiz' => [
        'max_size' => env('QUIZ_MAX_SIZE', 34359738368), // 32GB in bytes
        'max_size_mb' => env('QUIZ_MAX_SIZE_MB', 32768), // 32GB in MB for display
        'max_size_display' => '32GB', // User-friendly display text
        'mimes' => ['json', 'csv', 'xlsx'], // Quiz data formats
        'allowed_mime_types' => [
            'application/json',
            'text/csv',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
    ],

    'thumbnail' => [
        'max_size' => env('THUMBNAIL_MAX_SIZE', 5242880), // 5MB in bytes
        'max_size_mb' => 5, // 5MB in MB for display
        'max_size_display' => '5MB', // User-friendly display text
        'mimes' => ['jpeg', 'png', 'jpg', 'gif', 'webp'],
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ],
    ],

    'chunk' => [
        'size' => env('UPLOAD_CHUNK_SIZE', 10485760), // 10MB chunks for large file uploads
        'max_chunks' => env('UPLOAD_MAX_CHUNKS', 3277), // Max chunks for 32GB with 10MB chunks
    ],

    'temp_path' => env('UPLOAD_TEMP_PATH', 'storage/uploads/temp'),
    'video_path' => env('UPLOAD_VIDEO_PATH', 'storage/uploads/videos'),
    'document_path' => env('UPLOAD_DOCUMENT_PATH', 'storage/uploads/documents'),
    'quiz_path' => env('UPLOAD_QUIZ_PATH', 'storage/uploads/quizzes'),
    'thumbnail_path' => env('UPLOAD_THUMBNAIL_PATH', 'storage/uploads/thumbnails'),
];
