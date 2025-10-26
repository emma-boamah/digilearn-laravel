<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VideoSourceService
{
    const SOURCE_LOCAL = 'local';
    const SOURCE_YOUTUBE = 'youtube';
    const SOURCE_VIMEO = 'vimeo';
    const SOURCE_MUX = 'mux';

    /**
     * Supported video sources
     */
    public static function getSupportedSources()
    {
        return [
            self::SOURCE_LOCAL => 'Local Storage',
            self::SOURCE_YOUTUBE => 'YouTube',
            self::SOURCE_VIMEO => 'Vimeo',
            self::SOURCE_MUX => 'Mux',
        ];
    }

    /**
     * Parse video URL and extract source and ID
     */
    public static function parseVideoUrl($url)
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);

        // YouTube patterns
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
            return [
                'source' => self::SOURCE_YOUTUBE,
                'video_id' => $matches[1],
                'embed_url' => "https://www.youtube.com/embed/{$matches[1]}",
            ];
        }

        // Vimeo patterns
        if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $url, $matches)) {
            return [
                'source' => self::SOURCE_VIMEO,
                'video_id' => $matches[1],
                'embed_url' => "https://player.vimeo.com/video/{$matches[1]}",
            ];
        }

        // Mux patterns (playback URLs)
        if (preg_match('/stream\.mux\.com\/([a-zA-Z0-9]+)/', $url, $matches)) {
            return [
                'source' => self::SOURCE_MUX,
                'video_id' => $matches[1],
                'embed_url' => $url,
            ];
        }

        return null;
    }

    /**
     * Validate video URL for a specific source
     */
    public static function validateVideoUrl($url, $source = null)
    {
        $parsed = self::parseVideoUrl($url);

        if (!$parsed) {
            return false;
        }

        if ($source && $parsed['source'] !== $source) {
            return false;
        }

        return $parsed;
    }

    /**
     * Get embed HTML for a video
     */
    public static function getEmbedHtml($video)
    {
        $source = $video->video_source ?? self::SOURCE_LOCAL;
        $videoId = $video->external_video_id;
        $embedUrl = $video->external_video_url;

        Log::info('VideoSourceService::getEmbedHtml called', [
            'video_id' => $video->id ?? 'unknown',
            'source' => $source,
            'video_id_field' => $videoId,
            'embed_url' => $embedUrl,
            'mux_playback_id' => $video->mux_playback_id ?? 'none',
            'status' => $video->status ?? 'unknown'
        ]);

        switch ($source) {
            case self::SOURCE_YOUTUBE:
                if ($videoId) {
                    Log::info('VideoSourceService::getEmbedHtml - YouTube video', [
                        'video_id' => $video->id,
                        'youtube_video_id' => $videoId
                    ]);
                    return self::getYouTubeEmbedHtml($videoId);
                }
                break;

            case self::SOURCE_VIMEO:
                if ($videoId) {
                    Log::info('VideoSourceService::getEmbedHtml - Vimeo video', [
                        'video_id' => $video->id,
                        'vimeo_video_id' => $videoId
                    ]);
                    return self::getVimeoEmbedHtml($videoId);
                }
                break;

            case self::SOURCE_MUX:
                if ($video->mux_playback_id) {
                    Log::info('VideoSourceService::getEmbedHtml - Mux video', [
                        'video_id' => $video->id,
                        'mux_playback_id' => $video->mux_playback_id
                    ]);
                    return self::getMuxEmbedHtml($video->mux_playback_id);
                }
                break;

            case self::SOURCE_LOCAL:
            default:
                // For local videos, return streaming URL or null
                $videoUrl = $video->getVideoUrl();
                Log::info('VideoSourceService::getEmbedHtml - Local video', [
                    'video_id' => $video->id ?? 'unknown',
                    'video_url' => $videoUrl,
                    'video_path' => $video->video_path ?? 'none',
                    'temp_file_path' => $video->temp_file_path ?? 'none'
                ]);
                return $videoUrl;
        }

        Log::warning('VideoSourceService::getEmbedHtml - No embed HTML generated', [
            'video_id' => $video->id ?? 'unknown',
            'source' => $source,
            'video_id_field' => $videoId,
            'embed_url' => $embedUrl
        ]);
        return null;
    }

    /**
     * Get YouTube embed HTML
     */
    private static function getYouTubeEmbedHtml($videoId)
    {
        return '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
    }

    /**
     * Get Vimeo embed HTML
     */
    private static function getVimeoEmbedHtml($videoId)
    {
        return '<iframe src="https://player.vimeo.com/video/' . $videoId . '" width="560" height="315" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
    }

    /**
     * Get Mux embed HTML (using HLS.js or similar)
     */
    private static function getMuxEmbedHtml($playbackId)
    {
        $hlsUrl = "https://stream.mux.com/{$playbackId}.m3u8";
        return '<video id="mux-video-' . $playbackId . '" controls width="560" height="315">
            <source src="' . $hlsUrl . '" type="application/x-mpegURL">
            Your browser does not support the video tag.
        </video>
        <script>
            // Initialize HLS.js for Mux video
            if (Hls.isSupported()) {
                var video = document.getElementById("mux-video-' . $playbackId . '");
                var hls = new Hls();
                hls.loadSource("' . $hlsUrl . '");
                hls.attachMedia(video);
            }
        </script>';
    }

    /**
     * Extract video ID from URL
     */
    public static function extractVideoId($url, $source)
    {
        $parsed = self::parseVideoUrl($url);

        if ($parsed && $parsed['source'] === $source) {
            return $parsed['video_id'];
        }

        return null;
    }

    /**
     * Get canonical URL for a video
     */
    public static function getCanonicalUrl($video)
    {
        $source = $video->video_source ?? self::SOURCE_LOCAL;
        $videoId = $video->external_video_id;

        switch ($source) {
            case self::SOURCE_YOUTUBE:
                return $videoId ? "https://www.youtube.com/watch?v={$videoId}" : null;

            case self::SOURCE_VIMEO:
                return $videoId ? "https://vimeo.com/{$videoId}" : null;

            case self::SOURCE_MUX:
                return $video->mux_playback_id ? "https://stream.mux.com/{$video->mux_playback_id}" : null;

            case self::SOURCE_LOCAL:
            default:
                return $video->getVideoUrl();
        }
    }
}
