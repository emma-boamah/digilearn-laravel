<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EmailVerificationService
{
    protected $client;
    protected $apiKey;
    protected $baseUrl = 'http://apilayer.net/api/check';
    protected $cacheTtl = 86400; // 24 hours in seconds
    protected $cacheTag = 'email_verification';


    public function __construct()
    {
        $this->apiKey = config('services.mailboxlayer.key');
        $this->client = new Client([
            'timeout' => 5, // Seconds
        ]);
        $this->cacheTtl = (int) env('EMAIL_VERIFICATION_CACHE_TTL', 86400);
    }

    public function verify($email)
    {
        // Skip verification if disabled or no API key
        if (!config('services.mailboxlayer.enabled')) {
            return true;
        }

        // Generate unique cache key for the email
        $cacheKey = 'email:' . md5($email);

        // Return cached result if available using tags
        if (Cache::tags([$this->cacheTag])->has($cacheKey)) {
            return Cache::tags([$this->cacheTag])->get($cacheKey);
        }

        try {
            $response = $this->client->get($this->baseUrl, [
                'query' => [
                    'access_key' => $this->apiKey,
                    'email' => $email,
                    'smtp' => 1,
                    'format' => 1
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            // Validate API response
            $isValid = isset($data['format_valid'], $data['mx_found'], $data['smtp_check']) 
                        && $data['format_valid'] 
                        && $data['mx_found'] 
                        && $data['smtp_check'];

            // Cache the result with tags
            Cache::tags([$this->cacheTag])->put($cacheKey, $isValid, $this->cacheTtl);

            // Log verification
            $this->logVerification($email, $isValid, $data);

            // Check if email is valid
            return $isValid;
        } catch (\Exception $e) {
            Log::error('Email verification API error: '.$e->getMessage());

            // Cache failures for shorter period using tags
            Cache::tags([$this->cacheTag])->put($cacheKey, true, 3600); // 1 hour

            return true; // Fail open on API errors
        }
    }

    protected function logVerification($email, $result, $data)
    {
        Log::channel('email_verification')->info('Email verification result', [
            'email' => $email,
            'result' => $result ? 'valid' : 'invalid',
            'api_data' => $data,
            'cached_until' => now()->addSeconds($this->cacheTtl)->toDateTimeString(),
            'cache_tag' => $this->cacheTag
        ]);
    }

    /**
     * Clear cached verification results for a specific email
     */
    public function clearCacheForEmail($email)
    {
        $cacheKey = 'email:' . md5($email);
        Cache::tags([$this->cacheTag])->forget($cacheKey);
    }

    /**
     * Clear all cached email verification results
     */
    public function clearAllCache()
    {
        Cache::tags([$this->cacheTag])->flush();
    }
}