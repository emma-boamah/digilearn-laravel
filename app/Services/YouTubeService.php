<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class YouTubeService
{
    private $apiKey;
    private $baseUrl = 'https://www.googleapis.com/youtube/v3';

    public function __construct()
    {
        $this->apiKey = config('services.youtube.api_key');

        if (empty($this->apiKey)) {
            throw new Exception('YouTube API key is not configured.');
        }
    }

    /**
     * Get video information from YouTube
     */
    public function getVideoInfo($videoId)
    {
        try {
            $response = Http::get($this->baseUrl . '/videos', [
                'key' => $this->apiKey,
                'id' => $videoId,
                'part' => 'snippet,contentDetails,statistics',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['items'][0])) {
                    return $data['items'][0];
                }
            }

            Log::error('Failed to get YouTube video info', [
                'video_id' => $videoId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Exception getting YouTube video info', [
                'video_id' => $videoId,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Get video duration in seconds
     */
    public function getVideoDuration($videoId)
    {
        $videoInfo = $this->getVideoInfo($videoId);

        if (!$videoInfo || !isset($videoInfo['contentDetails']['duration'])) {
            return null;
        }

        // Parse ISO 8601 duration (PT4M13S = 4 minutes 13 seconds)
        $duration = $videoInfo['contentDetails']['duration'];
        return $this->parseDuration($duration);
    }

    /**
     * Parse ISO 8601 duration to seconds
     */
    private function parseDuration($duration)
    {
        $interval = new \DateInterval($duration);
        return ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
    }

    /**
     * Get video title
     */
    public function getVideoTitle($videoId)
    {
        $videoInfo = $this->getVideoInfo($videoId);

        if (!$videoInfo || !isset($videoInfo['snippet']['title'])) {
            return null;
        }

        return $videoInfo['snippet']['title'];
    }

    /**
     * Get video thumbnail URL
     */
    public function getVideoThumbnail($videoId)
    {
        $videoInfo = $this->getVideoInfo($videoId);

        if (!$videoInfo || !isset($videoInfo['snippet']['thumbnails'])) {
            return null;
        }

        $thumbnails = $videoInfo['snippet']['thumbnails'];

        // Prefer maxres, then high, then medium, then default
        if (isset($thumbnails['maxres'])) {
            return $thumbnails['maxres']['url'];
        } elseif (isset($thumbnails['high'])) {
            return $thumbnails['high']['url'];
        } elseif (isset($thumbnails['medium'])) {
            return $thumbnails['medium']['url'];
        } elseif (isset($thumbnails['default'])) {
            return $thumbnails['default']['url'];
        }

        return null;
    }

    /**
     * Get video statistics
     */
    public function getVideoStatistics($videoId)
    {
        $videoInfo = $this->getVideoInfo($videoId);

        if (!$videoInfo || !isset($videoInfo['statistics'])) {
            return null;
        }

        return $videoInfo['statistics'];
    }

    /**
     * Check if API key is valid
     */
    public function checkCredentials()
    {
        try {
            // Try to get info for a known public video
            $response = Http::get($this->baseUrl . '/videos', [
                'key' => $this->apiKey,
                'id' => 'dQw4w9WgXcQ', // Rick Astley - Never Gonna Give You Up
                'part' => 'snippet',
            ]);

            return $response->successful();

        } catch (Exception $e) {
            Log::error('YouTube credentials check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Search for videos (for future use)
     */
    public function searchVideos($query, $maxResults = 10)
    {
        try {
            $response = Http::get($this->baseUrl . '/search', [
                'key' => $this->apiKey,
                'q' => $query,
                'part' => 'snippet',
                'type' => 'video',
                'maxResults' => $maxResults,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to search YouTube videos', [
                'query' => $query,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Exception searching YouTube videos', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }
}