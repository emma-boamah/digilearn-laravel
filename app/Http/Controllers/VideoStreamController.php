<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VideoStreamController extends Controller
{
    /**
     * Stream video file with proper headers for browser playback
     */
    public function stream(Video $video, Request $request)
    {
        // Check if user has permission to view this video
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }

        // Get the video file path
        $filePath = null;
        if ($video->temp_file_path && !$video->isTempExpired()) {
            $filePath = $video->temp_file_path;
        } elseif ($video->video_path) {
            $filePath = $video->video_path;
        }

        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            abort(404, 'Video file not found');
        }

        $fullPath = Storage::disk('public')->path($filePath);
        $fileSize = filesize($fullPath);
        $mimeType = 'video/mp4';

        // Handle range requests for video seeking
        $start = 0;
        $end = $fileSize - 1;

        if ($request->hasHeader('Range')) {
            $range = $request->header('Range');
            if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
                $start = intval($matches[1]);
                if (!empty($matches[2])) {
                    $end = intval($matches[2]);
                }
            }
        }

        $length = $end - $start + 1;

        return new StreamedResponse(function() use ($fullPath, $start, $length) {
            $file = fopen($fullPath, 'rb');
            fseek($file, $start);
            
            $chunkSize = 8192; // 8KB chunks
            $bytesRemaining = $length;
            
            while (!feof($file) && $bytesRemaining > 0) {
                $bytesToRead = min($chunkSize, $bytesRemaining);
                echo fread($file, $bytesToRead);
                $bytesRemaining -= $bytesToRead;
                flush();
            }
            
            fclose($file);
        }, $request->hasHeader('Range') ? 206 : 200, [
            'Content-Type' => $mimeType,
            'Content-Length' => $length,
            'Accept-Ranges' => 'bytes',
            'Content-Range' => "bytes {$start}-{$end}/{$fileSize}",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
