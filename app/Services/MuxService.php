<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Exception;
use Illuminate\Support\Str;

class MuxService
{
    private $tokenId;
    private $tokenSecret;
    private $baseUrl = 'https://api.mux.com';
    private $timeout = 30; // seconds
    private $retryAttempts = 3;
    private $retryDelay = 1000; // milliseconds

    public function __construct()
    {
        $this->tokenId = config('services.mux.token_id');
        $this->tokenSecret = config('services.mux.token_secret');

        if (empty($this->tokenId) || empty($this->tokenSecret)) {
            throw new Exception('Mux credentials are not configured. Please check your .env file for MUX_TOKEN_ID and MUX_TOKEN_SECRET.');
        }

        // Validate credential format
        if (!Str::startsWith($this->tokenId, '') || !Str::startsWith($this->tokenSecret, '')) {
            Log::warning('Mux credentials may be malformed');
        }
    }

    /**
     * Create a direct upload URL for video upload
     */
    public function createDirectUpload($title = null, $description = null, array $additionalSettings = [])
    {
        // Validate input parameters
        if ($title && !is_string($title)) {
            throw new Exception('Title must be a string');
        }

        if ($description && !is_string($description)) {
            throw new Exception('Description must be a string');
        }

        // Try with mp4_support first (for paid accounts), fallback to basic (free accounts)
        $mp4SupportValues = ['standard', null]; // null means no mp4_support parameter

        foreach ($mp4SupportValues as $mp4Support) {
            $payload = array_merge([
                'new_asset_settings' => [
                    'playback_policy' => ['public'],
                ],
                'cors_origin' => '*',
                'test' => false,
            ], $additionalSettings);

            // Add mp4_support if specified
            if ($mp4Support !== null) {
                $payload['new_asset_settings']['mp4_support'] = $mp4Support;
            }

            // Add title and description if provided
            if ($title) {
                $payload['new_asset_settings']['input']['title'] = $title;
            }

            if ($description) {
                $payload['new_asset_settings']['input']['description'] = $description;
            }

            $result = $this->makeRequest('post', '/video/v1/uploads', $payload, function ($response) {
                $data = $response->json();

                if (!isset($data['data']['id']) || !isset($data['data']['url'])) {
                    throw new Exception('Invalid response format from Mux API');
                }

                Log::info('Mux direct upload created', [
                    'upload_id' => $data['data']['id'],
                    'mp4_support' => $payload['new_asset_settings']['mp4_support'] ?? 'none',
                    'url' => substr($data['data']['url'], 0, 50) . '...' // Log partial URL for security
                ]);

                return [
                    'success' => true,
                    'upload_id' => $data['data']['id'],
                    'upload_url' => $data['data']['url'],
                    'asset_id' => null,
                ];
            }, 'create Mux upload');

            // If successful, return the result
            if ($result['success']) {
                return $result;
            }

            // If the error is about deprecated mp4_support, try the next option
            if (isset($result['error']) && str_contains($result['error'], 'Deprecated \'standard\' mp4_support is not allowed')) {
                Log::info('Mux mp4_support not supported, trying without mp4_support', [
                    'tried_mp4_support' => $mp4Support,
                    'next_attempt' => $mp4Support === 'standard' ? 'none' : 'stop'
                ]);
                continue; // Try next option
            }

            // For other errors, return immediately
            return $result;
        }

        // If we get here, all attempts failed
        return [
            'success' => false,
            'error' => 'Failed to create Mux upload with any mp4_support configuration'
        ];
    }

    /**
     * Upload video file directly to Mux upload URL
     */
    public function uploadVideoFile($uploadUrl, $filePath)
    {
        // Validate inputs
        if (empty($uploadUrl) || !filter_var($uploadUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid upload URL provided');
        }

        if (empty($filePath)) {
            throw new Exception('File path is required');
        }

        $fullPath = storage_path('app/public/' . ltrim($filePath, '/'));

        // Log details
        Log::info('File upload details', [
            'provided_path' => $filePath,
            'full_path' => $fullPath,
            'file_exists' => file_exists($fullPath),
            'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0
        ]);


        // Comprehensive file validation
        if (!file_exists($fullPath)) {
            throw new Exception("Video file not found: {$fullPath}");
        }

        if (!is_readable($fullPath)) {
            throw new Exception("Video file is not readable: {$fullPath}");
        }

        $fileSize = filesize($fullPath);
        if ($fileSize === false || $fileSize === 0) {
            throw new Exception("Video file is empty or invalid: {$fullPath}");
        }

        // Check file size limit (Mux has a 256GB limit, but we'll set a practical limit)
        $maxFileSize = 10 * 1024 * 1024 * 1024; // 10GB
        if ($fileSize > $maxFileSize) {
            throw new Exception("Video file exceeds maximum size limit of 10GB");
        }

        // Detect MIME type
        $mimeType = $this->detectMimeType($fullPath);
        $allowedMimeTypes = [
            'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska',
            'video/webm', 'video/3gpp', 'video/mpeg', 'video/ogg'
        ];

        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new Exception("Unsupported video format: {$mimeType}");
        }

        $fileHandle = @fopen($fullPath, 'rb');
        if (!$fileHandle) {
            throw new Exception("Cannot open file for reading: {$fullPath}");
        }

        try {
            // Read file content
            $fileContent = fread($fileHandle, $fileSize);
            if ($fileContent === false) {
                throw new Exception("Failed to read file content: {$fullPath}");
            }

            // Upload with progress tracking and timeout
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => $mimeType,
                    'Content-Length' => $fileSize,
                ])
                ->withBody($fileContent, $mimeType)
                ->put($uploadUrl);

            if ($response->successful()) {
                Log::info('Video file uploaded to Mux successfully', [
                    'file_size' => $fileSize,
                    'mime_type' => $mimeType
                ]);
                return true;
            }

            // Handle specific HTTP status codes
            $statusCode = $response->status();
            $errorMessage = "Mux file upload failed with status: {$statusCode}";
            
            if ($statusCode === 413) {
                $errorMessage = "File too large for Mux upload";
            } elseif ($statusCode === 400) {
                $errorMessage = "Bad request - check file format and parameters";
            }

            Log::error($errorMessage, [
                'status' => $statusCode,
                'file_size' => $fileSize,
                'response' => $response->body()
            ]);

            return false;

        } catch (ConnectionException $e) {
            Log::error('Connection timeout during Mux file upload', [
                'error' => $e->getMessage(),
                'file_path' => $filePath,
                'timeout' => $this->timeout
            ]);
            return false;
        } finally {
            if (is_resource($fileHandle)) {
                fclose($fileHandle);
            }
        }
    }

    /**
     * Get upload status and asset information
     */
    public function getUploadStatus($uploadId)
    {
        if (empty($uploadId) || !is_string($uploadId)) {
            throw new Exception('Invalid upload ID provided');
        }

        return $this->makeRequest('get', "/video/v1/uploads/{$uploadId}", [], function ($response) {
            $data = $response->json()['data'];

            return [
                'success' => true,
                'status' => $data['status'],
                'asset_id' => $data['asset_id'] ?? null,
                'error_message' => $data['error_message'] ?? null,
                'cancelled' => $data['cancelled'] ?? false,
            ];
        }, 'get upload status');
    }

    /**
     * Get asset information
     */
    public function getAsset($assetId)
    {
        if (empty($assetId) || !is_string($assetId)) {
            throw new Exception('Invalid asset ID provided');
        }

        return $this->makeRequest('get', "/video/v1/assets/{$assetId}", [], function ($response) {
            $data = $response->json()['data'];

            return [
                'success' => true,
                'asset_id' => $data['id'],
                'status' => $data['status'],
                'playback_id' => $data['playback_ids'][0]['id'] ?? null,
                'duration' => $data['duration'] ?? null,
                'max_stored_resolution' => $data['max_stored_resolution'] ?? null,
                'max_stored_frame_rate' => $data['max_stored_frame_rate'] ?? null,
                'created_at' => $data['created_at'],
                'mp4_support' => $data['mp4_support'] ?? null,
                'aspect_ratio' => $data['aspect_ratio'] ?? null,
            ];
        }, 'get asset information');
    }

    /**
     * Wait for asset to be ready with timeout
     */
    public function waitForAssetReady($assetId, $timeout = 300, $checkInterval = 5)
    {
        $startTime = time();
        
        while ((time() - $startTime) < $timeout) {
            $assetInfo = $this->getAsset($assetId);
            
            if (!$assetInfo['success']) {
                throw new Exception("Failed to get asset status: {$assetInfo['error']}");
            }
            
            if ($assetInfo['status'] === 'ready') {
                return $assetInfo;
            }
            
            if ($assetInfo['status'] === 'errored') {
                throw new Exception("Asset processing failed");
            }
            
            sleep($checkInterval);
        }
        
        throw new Exception("Timeout waiting for asset to be ready");
    }

    /**
     * Get playback URL for a video
     */
    public function getPlaybackUrl($playbackId)
    {
        if (empty($playbackId)) {
            throw new Exception('Playback ID is required');
        }
        
        return "https://stream.mux.com/{$playbackId}.m3u8";
    }

    /**
     * Get MP4 download URL
     */
    public function getMp4Url($playbackId, $quality = 'low')
    {
        if (empty($playbackId)) {
            throw new Exception('Playback ID is required');
        }
        
        $allowedQualities = ['low', 'medium', 'high'];
        if (!in_array($quality, $allowedQualities)) {
            $quality = 'low';
        }
        
        return "https://stream.mux.com/{$playbackId}/{$quality}.mp4";
    }

    /**
     * Delete asset from Mux
     */
    public function deleteAsset($assetId)
    {
        if (empty($assetId)) {
            throw new Exception('Asset ID is required for deletion');
        }

        try {
            $response = Http::withBasicAuth($this->tokenId, $this->tokenSecret)
                ->timeout($this->timeout)
                ->retry($this->retryAttempts, $this->retryDelay)
                ->delete($this->baseUrl . "/video/v1/assets/{$assetId}");

            $success = $response->successful();

            if ($success) {
                Log::info('Mux asset deleted successfully', ['asset_id' => $assetId]);
            } else {
                Log::error('Failed to delete Mux asset', [
                    'asset_id' => $assetId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }

            return $success;

        } catch (Exception $e) {
            Log::error('Exception deleting Mux asset', [
                'asset_id' => $assetId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Check if Mux credentials are valid
     */
    public function checkCredentials()
    {
        try {
            $response = Http::withBasicAuth($this->tokenId, $this->tokenSecret)
                ->timeout(10)
                ->get($this->baseUrl . '/video/v1/assets?limit=1');

            return $response->successful();

        } catch (Exception $e) {
            Log::error('Mux credentials check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Main upload method that handles the complete flow
     */
    public function uploadVideo($filePath, $title, $description = null, $waitForReady = false)
    {
        try {
            Log::info('Starting Mux upload process', [
                'file_path' => $filePath,
                'title' => $title,
                'description_length' => $description ? strlen($description) : 0
            ]);

            // Step 1: Create direct upload
            $uploadResult = $this->createDirectUpload($title, $description);

            if (!$uploadResult['success']) {
                throw new Exception('Failed to create Mux upload: ' . ($uploadResult['error'] ?? 'Unknown error'));
            }

            // Step 2: Upload the file
            $uploadSuccess = $this->uploadVideoFile($uploadResult['upload_url'], $filePath);

            if (!$uploadSuccess) {
                // Attempt to clean up the upload if file upload fails
                $this->cleanupFailedUpload($uploadResult['upload_id']);
                throw new Exception('Failed to upload video file to Mux');
            }

            // Step 3: Poll for upload status with retries
            $maxPollAttempts = 12; // 60 seconds total with 5s intervals
            $pollAttempt = 0;
            $assetId = null;

            while ($pollAttempt < $maxPollAttempts) {
                $statusResult = $this->getUploadStatus($uploadResult['upload_id']);
                
                if (!$statusResult['success']) {
                    throw new Exception('Failed to get upload status: ' . ($statusResult['error'] ?? 'Unknown error'));
                }

                if ($statusResult['asset_id']) {
                    $assetId = $statusResult['asset_id'];
                    break;
                }

                if ($statusResult['status'] === 'errored') {
                    throw new Exception('Upload processing failed: ' . ($statusResult['error_message'] ?? 'Unknown error'));
                }

                $pollAttempt++;
                sleep(5); // Wait 5 seconds before next poll
            }

            if (!$assetId) {
                throw new Exception('Timeout waiting for asset ID after upload');
            }

            $result = [
                'success' => true,
                'upload_id' => $uploadResult['upload_id'],
                'asset_id' => $assetId,
                'status' => 'processing',
            ];

            // Step 4: Wait for asset to be ready if requested
            if ($waitForReady) {
                try {
                    $assetInfo = $this->waitForAssetReady($assetId);
                    $result = array_merge($result, [
                        'status' => $assetInfo['status'],
                        'playback_id' => $assetInfo['playback_id'],
                        'playback_url' => $this->getPlaybackUrl($assetInfo['playback_id']),
                        'mp4_url' => $this->getMp4Url($assetInfo['playback_id']),
                        'duration' => $assetInfo['duration'],
                    ]);
                } catch (Exception $e) {
                    Log::warning('Failed to wait for asset ready', ['error' => $e->getMessage()]);
                    // Continue with basic result even if waiting fails
                }
            }

            Log::info('Mux upload process completed successfully', [
                'upload_id' => $uploadResult['upload_id'],
                'asset_id' => $assetId
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error('Mux upload failed', [
                'error' => $e->getMessage(),
                'file_path' => $filePath,
                'title' => $title,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generic request method with retry logic and error handling
     */
    private function makeRequest($method, $endpoint, $data = [], callable $successHandler = null, $operation = 'API operation')
    {
        $url = $this->baseUrl . $endpoint;

        try {
            $response = Http::withBasicAuth($this->tokenId, $this->tokenSecret)
                ->timeout($this->timeout)
                ->retry($this->retryAttempts, $this->retryDelay, function ($exception, $request) use ($operation) {
                    Log::warning("Retrying Mux {$operation} after exception", ['error' => $exception->getMessage()]);
                    return $exception instanceof ConnectionException;
                })
                ->{$method}($url, $data);

            if ($response->successful()) {
                if ($successHandler) {
                    return $successHandler($response);
                }
                return ['success' => true, 'data' => $response->json()];
            }

            // Handle specific HTTP errors
            $statusCode = $response->status();
            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? "HTTP {$statusCode}";

            if ($statusCode === 401) {
                $errorMessage = 'Mux authentication failed - check your credentials';
            } elseif ($statusCode === 403) {
                $errorMessage = 'Mux API access forbidden';
            } elseif ($statusCode === 429) {
                $errorMessage = 'Mux API rate limit exceeded';
            }

            Log::error("Mux {$operation} failed", [
                'status' => $statusCode,
                'endpoint' => $endpoint,
                'error' => $errorMessage,
                'response' => $errorData
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'status_code' => $statusCode
            ];

        } catch (ConnectionException $e) {
            Log::error("Mux {$operation} connection failed", [
                'error' => $e->getMessage(),
                'endpoint' => $endpoint,
                'timeout' => $this->timeout
            ]);

            return [
                'success' => false,
                'error' => "Connection timeout: {$e->getMessage()}"
            ];
        } catch (Exception $e) {
            Log::error("Exception during Mux {$operation}", [
                'error' => $e->getMessage(),
                'endpoint' => $endpoint
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Detect MIME type of file
     */
    private function detectMimeType($filePath)
    {
        // First try finfo
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            
            if ($mimeType && $mimeType !== 'application/octet-stream') {
                return $mimeType;
            }
        }

        // Fallback to extension detection
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeMap = [
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'mkv' => 'video/x-matroska',
            'webm' => 'video/webm',
            '3gp' => 'video/3gpp',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'ogg' => 'video/ogg',
        ];

        return $mimeMap[$extension] ?? 'application/octet-stream';
    }

    /**
     * Clean up failed upload
     */
    private function cleanupFailedUpload($uploadId)
    {
        try {
            if ($uploadId) {
                Http::withBasicAuth($this->tokenId, $this->tokenSecret)
                    ->delete($this->baseUrl . "/video/v1/uploads/{$uploadId}");
            }
        } catch (Exception $e) {
            Log::warning('Failed to clean up Mux upload', [
                'upload_id' => $uploadId,
                'error' => $e->getMessage()
            ]);
        }
    }
}