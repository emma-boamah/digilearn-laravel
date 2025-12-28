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

    public function verify(string $email): array
    {
        // Skip verification if disabled or no API key
        if (!config('services.mailboxlayer.enabled', false)) {
            return [
                'valid' => true,
                'message' => null,
                'service_disabled' => true
            ];
        }

        // Generate unique cache key for the email
        $cacheKey = 'email:' . md5($email);

        // Return cached result if available using tags
        if (Cache::tags([$this->cacheTag])->has($cacheKey)) {
            $cached = Cache::tags([$this->cacheTag])->get($cacheKey);
            return [
                'valid' => $cached,
                'message' => null,
                'cached' => true
            ];
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
            return [
                'valid' => $isValid,
                'message' => $isValid ? null : $this->getErrorMessage($data),
                'data' => $data
            ];
        } catch (\Exception $e) {
            Log::error('Email verification API error: '.$e->getMessage(), [
                'email' => $email,
                'error' => $e->getMessage(),
                'exception' => get_class($e)
            ]);

            // On API errors, reject the email to prevent abuse
            // Cache failures for shorter period using tags
            Cache::tags([$this->cacheTag])->put($cacheKey, false, 3600); // 1 hour

            return [
                'valid' => false, // Fail closed on API errors
                'message' => 'Email verification service is temporarily unavailable. Please try again later or contact support.',
                'service_error' => true,
                'error' => $e->getMessage()
            ];
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

    /**
     * Get error message based on API response
     */
    private function getErrorMessage(array $data): ?string
    {
        $messages = [
            'format_invalid' => 'This email address format is invalid.',
            'mx_not_found' => 'The email domain does not exist or is not configured properly.',
            'smtp_check_failed' => 'The email server rejected this address.',
        ];

        if (!$data['format_valid']) {
            return $messages['format_invalid'];
        }

        if (!$data['mx_found']) {
            return $messages['mx_not_found'];
        }

        if (!$data['smtp_check']) {
            return $messages['smtp_check_failed'];
        }

        return 'This email address appears to be invalid or undeliverable.';
    }
}