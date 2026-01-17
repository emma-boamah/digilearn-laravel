<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Events\UploadProgress;
use Exception;

class VimeoService
{
    private $accessToken;
    private $baseUrl = 'https://api.vimeo.com';

    public function __construct()
    {
        $this->accessToken = config('services.vimeo.access_token');

        if (empty($this->accessToken)) {
            throw new Exception('Vimeo access token is not configured.');
        }
    }

    /**
     * Upload video to Vimeo
     */
    public function uploadVideo($filePath, $title, $description = null, $userId = null, $uploadId = null)
    {
        try {
            Log::info('Starting Vimeo upload process', ['file_path' => $filePath, 'title' => $title]);

            // Validate file existence - handle both relative and absolute paths
            if (str_starts_with($filePath, '/')) {
                // Absolute path
                $fullPath = $filePath;
            } else {
                // Normalize relative path by stripping any storage prefixes
                $relativePath = $filePath;
                if (str_starts_with($relativePath, 'storage/public/')) {
                    $relativePath = substr($relativePath, strlen('storage/public/'));
                } elseif (str_starts_with($relativePath, 'storage/')) {
                    $relativePath = substr($relativePath, strlen('storage/'));
                }
                // Use Storage facade to get the full path
                $fullPath = Storage::disk('public')->path($relativePath);
            }

            if (!file_exists($fullPath)) {
                throw new Exception("Video file not found: {$fullPath}");
            }

            // Get the file size
            $fileSize = filesize($fullPath);
            Log::info('File details', ['path' => $fullPath, 'size' => $fileSize]);

            // Step 1: Create video entry on Vimeo
            $videoData = $this->createVideoEntry($title, $fileSize, $description);
            
            if (!$videoData || !isset($videoData['upload']['upload_link'])) {
                throw new Exception('Failed to create video entry on Vimeo: ' . ($videoData['error'] ?? 'Unknown error'));
            }

            Log::info('Video entry created', ['uri' => $videoData['uri']]);

            // Step 2: Upload the actual video file
            $uploadSuccess = $this->uploadVideoFile($videoData['upload']['upload_link'], $fullPath, $userId, $uploadId, $fileSize);
            
            if (!$uploadSuccess) {
                throw new Exception('Failed to upload video file to Vimeo');
            }

            Log::info('Video file uploaded successfully');

            // Step 3: Verify the upload was successful
            $videoId = str_replace('/videos/', '', $videoData['uri']);
            $verification = $this->verifyVideoUpload($videoId);
            
            if (!$verification['success']) {
                throw new Exception('Video upload verification failed: ' . ($verification['error'] ?? 'Unknown error'));
            }

            Log::info('Video upload verified', ['videoId' => $videoId, 'status' => $verification['status']]);

            return [
                'success' => true,
                'video_id' => $videoId,
                'embed_url' => "https://player.vimeo.com/video/{$videoId}",
                'vimeo_url' => "https://vimeo.com/{$videoId}",
                'uri' => $videoData['uri'],
                'status' => $verification['status']
            ];

        } catch (Exception $e) {
            Log::error('Vimeo upload failed', [
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
     * Create video entry on Vimeo
     */
    private function createVideoEntry($title, $fileSize, $description = null)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/vnd.vimeo.*+json;version=3.4'
            ])->post($this->baseUrl . '/me/videos', [
                'upload' => [
                    'approach' => 'tus', // Use tus for resumable uploads
                    'size' => $fileSize
                ],
                'name' => $title,
                'description' => $description,
                'privacy' => [
                    'view' => 'anybody', // Allow public viewing
                    'embed' => 'public'  // Allow embedding on any site
                ]
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            $errorResponse = $response->json();
            Log::error('Failed to create Vimeo video entry', [
                'status' => $response->status(),
                'response' => $errorResponse,
                'headers' => $response->headers()
            ]);

            return [
                'error' => $errorResponse['error'] ?? 'HTTP ' . $response->status()
            ];

        } catch (Exception $e) {
            Log::error('Exception creating Vimeo video entry', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Upload video file to Vimeo
     */
    private function uploadVideoFile($uploadUrl, $fullPath, $userId = null, $uploadId = null, $fileSize = null)
    {
        try {
            $fileSize = filesize($fullPath);
            $chunkSize = 2 * 1024 * 1024; // 2MB chunks

            $fileHandle = fopen($fullPath, 'rb');
            if (!$fileHandle) {
                throw new Exception("Cannot open file: {$fullPath}");
            }

            $offset = 0;

            while ($offset < $fileSize) {
                $chunk = fread($fileHandle, $chunkSize);
                $chunkSizeActual = strlen($chunk);

                $response = Http::withHeaders([
                    'Content-Type' => 'application/offset+octet-stream',
                    'Upload-Offset' => $offset,
                    'Tus-Resumable' => '1.0.0'
                ])->withBody($chunk, 'application/offset+octet-stream')
                  ->patch($uploadUrl);

                if (!$response->successful()) {
                    fclose($fileHandle);
                    Log::error('TUS upload chunk failed', [
                        'offset' => $offset,
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    return false;
                }

                $offset += $chunkSizeActual;

                // Broadcast progress for all uploads with user context
                if ($userId && $uploadId) {
                    $progress = round(($offset / $fileSize) * 100, 2);
                    $uploadedBytes = $offset;
                    $remainingBytes = $fileSize - $uploadedBytes;

                    // Calculate speed (rough estimate based on chunk size and time)
                    $speed = $chunkSizeActual; // bytes per chunk, rough approximation

                    // Estimate time remaining (rough approximation)
                    $timeRemaining = $speed > 0 ? $remainingBytes / $speed : null;

                    broadcast(new UploadProgress(
                        $userId,
                        $uploadId,
                        $progress,
                        'Uploading video to Vimeo...',
                        $uploadedBytes,
                        $fileSize,
                        $speed,
                        $timeRemaining
                    ));

                    Log::info('Upload progress', ['progress' => $progress . '%', 'user_id' => $userId, 'upload_id' => $uploadId]);
                }
            }

            fclose($fileHandle);
            return true;

        } catch (Exception $e) {
            Log::error('TUS upload failed', [
                'error' => $e->getMessage(),
                'file' => $fullPath
            ]);
            return false;
        }
    }

    /**
     * Verify that video was successfully uploaded to Vimeo
     */
    public function verifyVideoUpload($videoId)
    {
        try {
            // Wait a moment for Vimeo to process
            sleep(5);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Accept' => 'application/vnd.vimeo.*+json;version=3.4'
            ])->get($this->baseUrl . "/videos/{$videoId}");

            if ($response->successful()) {
                $videoData = $response->json();
                
                return [
                    'success' => true,
                    'status' => $videoData['status'] ?? 'unknown',
                    'embed_url' => $videoData['embed']['html'] ?? null,
                    'name' => $videoData['name'] ?? null
                ];
            }

            return [
                'success' => false,
                'error' => 'HTTP ' . $response->status() . ': ' . $response->body()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete video from Vimeo
     */
    public function deleteVideo($videoId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Accept' => 'application/vnd.vimeo.*+json;version=3.4'
            ])->delete($this->baseUrl . "/videos/{$videoId}");

            $success = $response->successful();
            
            if (!$success) {
                Log::error('Failed to delete Vimeo video', [
                    'video_id' => $videoId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }

            return $success;

        } catch (Exception $e) {
            Log::error('Exception deleting Vimeo video', [
                'video_id' => $videoId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get video information from Vimeo
     */
    public function getVideoInfo($videoId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Accept' => 'application/vnd.vimeo.*+json;version=3.4'
            ])->get($this->baseUrl . "/videos/{$videoId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to get Vimeo video info', [
                'video_id' => $videoId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Exception getting Vimeo video info', [
                'video_id' => $videoId,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Get video status from Vimeo
     */
    public function getVideoStatus($videoId)
    {
        $videoInfo = $this->getVideoInfo($videoId);

        if (!$videoInfo) {
            return null;
        }

        return [
            'status' => $videoInfo['status'] ?? null, // e.g., 'available', 'transcoding', 'uploading'
            'transcode' => $videoInfo['transcode'] ?? null,
            'upload' => $videoInfo['upload'] ?? null,
            'embed_url' => $videoInfo['player_embed_url'] ?? null,
        ];
    }

    /**
     * Update video privacy settings to allow embedding
     */
    public function updateVideoPrivacy($videoId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/vnd.vimeo.*+json;version=3.4'
            ])->patch($this->baseUrl . "/videos/{$videoId}", [
                'privacy' => [
                    'view' => 'anybody', // Allow public viewing
                    'embed' => 'public'  // Allow embedding on any site
                ]
            ]);

            if ($response->successful()) {
                Log::info('Successfully updated Vimeo video privacy settings', ['video_id' => $videoId]);
                return true;
            }

            Log::error('Failed to update Vimeo video privacy', [
                'video_id' => $videoId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return false;

        } catch (Exception $e) {
            Log::error('Exception updating Vimeo video privacy', [
                'video_id' => $videoId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Fix privacy settings for all Vimeo videos in the database
     */
    public function fixAllVimeoVideoPrivacy()
    {
        try {
            $videos = \App\Models\Video::where('video_source', 'vimeo')
                ->whereNotNull('vimeo_id')
                ->get();

            $successCount = 0;
            $failCount = 0;

            foreach ($videos as $video) {
                if ($this->updateVideoPrivacy($video->vimeo_id)) {
                    $successCount++;
                    Log::info('Fixed privacy for video', ['video_id' => $video->id, 'vimeo_id' => $video->vimeo_id]);
                } else {
                    $failCount++;
                    Log::error('Failed to fix privacy for video', ['video_id' => $video->id, 'vimeo_id' => $video->vimeo_id]);
                }
            }

            return [
                'success' => true,
                'message' => "Privacy settings updated for {$successCount} videos, {$failCount} failed",
                'updated' => $successCount,
                'failed' => $failCount
            ];

        } catch (Exception $e) {
            Log::error('Exception fixing Vimeo video privacy', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if Vimeo credentials are valid
     */
    public function checkCredentials()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Accept' => 'application/vnd.vimeo.*+json;version=3.4'
            ])->get($this->baseUrl . '/me');

            return $response->successful();

        } catch (Exception $e) {
            Log::error('Vimeo credentials check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
