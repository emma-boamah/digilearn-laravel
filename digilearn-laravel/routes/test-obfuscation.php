<?php

use App\Services\UrlObfuscator;
use App\Models\Video;
use App\Models\Course;
use Illuminate\Support\Facades\Route;

// Test URL obfuscation with Laravel Crypt + SEO slugs
Route::get('/test-obfuscation', function () {
    $testId = 123;
    $testTitle = "Introduction to Mathematics";

    // Test encoding/decoding
    $encoded = UrlObfuscator::encode($testId);
    $decoded = UrlObfuscator::decode($encoded);

    // Test SEO URL creation/parsing
    $seoUrl = UrlObfuscator::createSeoUrl($testId, $testTitle);
    $parsed = UrlObfuscator::parseSeoUrl($seoUrl);

    $results = [
        'basic_encoding' => [
            'original_id' => $testId,
            'encrypted' => $encoded,
            'decrypted' => $decoded,
            'successful' => $testId === $decoded,
        ],
        'seo_urls' => [
            'title' => $testTitle,
            'seo_url' => $seoUrl,
            'parsed' => $parsed,
            'parse_successful' => $parsed && $parsed['id'] === $testId,
        ],
    ];

    // Test with models if they exist
    $video = Video::first();
    $course = Course::first();

    if ($video) {
        $results['video'] = [
            'id' => $video->id,
            'title' => $video->title,
            'seo_url' => $video->seo_url,
            'slug' => $video->slug,
            'parsed' => UrlObfuscator::parseSeoUrl($video->seo_url),
        ];
    }

    if ($course) {
        $results['course'] = [
            'id' => $course->id,
            'title' => $course->title,
            'seo_url' => $course->seo_url,
            'slug' => $course->slug,
            'parsed' => UrlObfuscator::parseSeoUrl($course->seo_url),
        ];
    }

    return response()->json($results, JSON_PRETTY_PRINT);
});