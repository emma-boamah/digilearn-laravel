<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VideoStreamController extends Controller
{
    /**
     * Helper method to get level group from grade level
     */
    private function getLevelGroup($level)
    {
        $groups = [
            'primary-1' => 'primary-lower',
            'primary-2' => 'primary-lower',
            'primary-3' => 'primary-lower',
            'primary-4' => 'primary-upper',
            'primary-5' => 'primary-upper',
            'primary-6' => 'primary-upper',
            'jhs-1' => 'jhs',
            'jhs-2' => 'jhs',
            'jhs-3' => 'jhs',
            'shs-1' => 'shs',
            'shs-2' => 'shs',
            'shs-3' => 'shs',
            'Primary 1' => 'primary-lower',
            'Primary 2' => 'primary-lower',
            'Primary 3' => 'primary-lower',
            'Primary 4' => 'primary-upper',
            'Primary 5' => 'primary-upper',
            'Primary 6' => 'primary-upper',
            'JHS 1' => 'jhs',
            'JHS 2' => 'jhs',
            'JHS 3' => 'jhs',
            'SHS 1' => 'shs',
            'SHS 2' => 'shs',
            'SHS 3' => 'shs',
        ];

        return $groups[$level] ?? $level;
    }

    /**
     * Stream video file with proper headers for browser playback
     */
    public function stream($id, Request $request)
    {
        $video = Video::findOrFail($id);

        // Check if user has permission to view this video
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        // Skip logging for admin preview to avoid errors
        $isAdmin = Auth::user()->is_admin || Auth::user()->is_superuser;
        if (!$isAdmin) {
            // Log video watching activity
            \App\Models\ActivityLog::log(
                'video_watch',
                'User watched video: ' . $video->title,
                'info',
                Auth::id(),
                Auth::user()->email,
                $request->ip(),
                $request->userAgent(),
                [
                    'video_id' => $video->id,
                    'video_title' => $video->title,
                    'grade_level' => $video->grade_level,
                    'duration_seconds' => $video->duration_seconds,
                    'subject' => $video->subject ?? 'General',
                    'action_type' => 'stream_start'
                ],
                $video
            );

            // Record detailed engagement for recommendation system
            \App\Models\UserEngagement::record(
                Auth::id(),
                'video',
                $video->id,
                'view',
                0, // duration will be tracked separately
                [
                    'title' => $video->title,
                    'subject' => $video->subject ?? 'General',
                    'grade_level' => $video->grade_level,
                    'duration_seconds' => $video->duration_seconds,
                    'action_type' => 'stream_start'
                ]
            );
        }


        // Get the video file path
        $filePath = null;
        if ($video->temp_file_path && !$video->isTempExpired()) {
            $filePath = $video->temp_file_path;
        } elseif ($video->video_path) {
            $filePath = $video->video_path;
        }

        if (!$filePath) {
            Log::error('Video stream failed: No file path available', [
                'video_id' => $video->id,
                'temp_file_path' => $video->temp_file_path,
                'video_path' => $video->video_path,
                'is_temp_expired' => $video->isTempExpired(),
                'status' => $video->status
            ]);
            abort(404, 'Video file not found - no file path available');
        }

        if (!Storage::disk('public')->exists($filePath)) {
            Log::error('Video stream failed: File does not exist in storage', [
                'video_id' => $video->id,
                'file_path' => $filePath,
                'full_path' => Storage::disk('public')->path($filePath),
                'file_exists' => file_exists(Storage::disk('public')->path($filePath)),
                'status' => $video->status
            ]);
            abort(404, 'Video file not found - file does not exist in storage');
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
