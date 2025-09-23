<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class VimeoService
{
    private $accessToken;
    private $baseUrl = 'https://api.vimeo.com';

    public function __construct()
    {
        $this->accessToken = config('services.vimeo.access_token');
    }

    /**
     * Upload video to Vimeo
     */
    public function uploadVideo($filePath, $title, $description = null)
    {
        try {
            // Step 1: Create video entry
            $videoData = $this->createVideoEntry($title, $description);
            
            if (!$videoData) {
                throw new Exception('Failed to create video entry on Vimeo');
            }

            // Step 2: Upload the actual video file
            $uploadSuccess = $this->uploadVideoFile($videoData['upload']['upload_link'], $filePath);
            
            if (!$uploadSuccess) {
                throw new Exception('Failed to upload video file to Vimeo');
            }

            // Step 3: Complete the upload
            $completeSuccess = $this->completeUpload($videoData['uri']);
            
            if (!$completeSuccess) {
                throw new Exception('Failed to complete video upload on Vimeo');
            }

            // Extract video ID from URI (e.g., "/videos/123456789" -> "123456789")
            $videoId = str_replace('/videos/', '', $videoData['uri']);

            return [
                'success' => true,
                'video_id' => $videoId,
                'embed_url' => "https://player.vimeo.com/video/{$videoId}",
                'vimeo_url' => "https://vimeo.com/{$videoId}",
                'uri' => $videoData['uri']
            ];

        } catch (Exception $e) {
            Log::error('Vimeo upload failed', [
                'error' => $e->getMessage(),
                'file_path' => $filePath,
                'title' => $title
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
    private function createVideoEntry($title, $description = null)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/vnd.vimeo.*+json;version=3.4'
        ])->post($this->baseUrl . '/me/videos', [
            'upload' => [
                'approach' => 'post',
                'size' => filesize(storage_path('app/public/' . ltrim($filePath, 'storage/')))
            ],
            'name' => $title,
            'description' => $description,
            'privacy' => [
                'view' => 'unlisted' // or 'anybody', 'nobody', 'contacts', 'password'
            ]
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Failed to create Vimeo video entry', [
            'status' => $response->status(),
            'response' => $response->body()
        ]);

        return null;
    }

    /**
     * Upload video file to Vimeo
     */
    private function uploadVideoFile($uploadUrl, $filePath)
    {
        $fullPath = storage_path('app/public/' . ltrim($filePath, 'storage/'));
        
        if (!file_exists($fullPath)) {
            throw new Exception("Video file not found: {$fullPath}");
        }

        $response = Http::attach(
            'file_data', 
            file_get_contents($fullPath), 
            basename($fullPath)
        )->post($uploadUrl);

        return $response->successful();
    }

    /**
     * Complete the upload process
     */
    private function completeUpload($videoUri)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept' => 'application/vnd.vimeo.*+json;version=3.4'
        ])->patch($this->baseUrl . $videoUri, [
            'upload' => [
                'approach' => 'post'
            ]
        ]);

        return $response->successful();
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

            return $response->successful();

        } catch (Exception $e) {
            Log::error('Failed to delete Vimeo video', [
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

            return null;

        } catch (Exception $e) {
            Log::error('Failed to get Vimeo video info', [
                'video_id' => $videoId,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }
}
