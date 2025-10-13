<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Video;

class MuxWebhookController extends Controller
{
    /**
     * Handle Mux webhook events
     */
    public function handleWebhook(Request $request)
    {
        try {
            // Verify webhook signature
            if (!$this->verifyWebhookSignature($request)) {
                Log::warning('Mux webhook signature verification failed', [
                    'headers' => $request->headers->all(),
                    'body' => $request->getContent()
                ]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $payload = $request->all();
            $eventType = $payload['type'] ?? null;
            $data = $payload['data'] ?? [];

            Log::info('Mux webhook received', [
                'event_type' => $eventType,
                'data' => $data
            ]);

            switch ($eventType) {
                case 'video.asset.ready':
                    $this->handleVideoAssetReady($data);
                    break;

                case 'video.asset.errored':
                    $this->handleVideoAssetErrored($data);
                    break;

                case 'video.upload.cancelled':
                    $this->handleVideoUploadCancelled($data);
                    break;

                case 'video.asset.deleted':
                    $this->handleVideoAssetDeleted($data);
                    break;

                default:
                    Log::info('Unhandled Mux webhook event', ['event_type' => $eventType]);
            }

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('Mux webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Verify Mux webhook signature
     */
    private function verifyWebhookSignature(Request $request): bool
    {
        $signature = $request->header('Mux-Signature');
        $webhookSecret = config('services.mux.webhook_secret');

        if (!$signature || !$webhookSecret) {
            return false;
        }

        // Mux sends signature in format: t=timestamp,v1=signature
        $parts = explode(',', $signature);
        if (count($parts) !== 2) {
            return false;
        }

        $timestamp = str_replace('t=', '', $parts[0]);
        $expectedSignature = str_replace('v1=', '', $parts[1]);

        // Create the expected signature
        $payload = $timestamp . '.' . $request->getContent();
        $computedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        // Use timing-safe comparison
        return hash_equals($expectedSignature, $computedSignature);
    }

    /**
     * Handle video asset ready event
     */
    private function handleVideoAssetReady(array $data)
    {
        $assetId = $data['id'] ?? null;
        $playbackIds = $data['playback_ids'] ?? [];
        $status = $data['status'] ?? null;

        if (!$assetId) {
            Log::warning('Mux webhook: Missing asset ID in video.asset.ready');
            return;
        }

        // Find video by mux_asset_id
        $video = Video::where('mux_asset_id', $assetId)->first();

        if (!$video) {
            Log::warning('Mux webhook: Video not found for asset ID', ['asset_id' => $assetId]);
            return;
        }

        // Update video with playback information
        $playbackId = null;
        foreach ($playbackIds as $playback) {
            if ($playback['policy'] === 'public') {
                $playbackId = $playback['id'];
                break;
            }
        }

        $video->update([
            'status' => 'approved',
            'mux_playback_id' => $playbackId,
        ]);

        Log::info('Video marked as approved via Mux webhook', [
            'video_id' => $video->id,
            'asset_id' => $assetId,
            'playback_id' => $playbackId
        ]);

        // Clean up temporary files
        if ($video->temp_file_path) {
            Storage::disk('public')->delete($video->temp_file_path);
            $video->update([
                'temp_file_path' => null,
                'temp_expires_at' => null
            ]);
        }
    }

    /**
     * Handle video asset errored event
     */
    private function handleVideoAssetErrored(array $data)
    {
        $assetId = $data['id'] ?? null;
        $errors = $data['errors'] ?? [];

        if (!$assetId) {
            Log::warning('Mux webhook: Missing asset ID in video.asset.errored');
            return;
        }

        $video = Video::where('mux_asset_id', $assetId)->first();

        if (!$video) {
            Log::warning('Mux webhook: Video not found for asset ID', ['asset_id' => $assetId]);
            return;
        }

        $video->update(['status' => 'rejected']);

        Log::error('Video processing failed via Mux webhook', [
            'video_id' => $video->id,
            'asset_id' => $assetId,
            'errors' => $errors
        ]);

        // Clean up temporary files
        if ($video->temp_file_path) {
            Storage::disk('public')->delete($video->temp_file_path);
            $video->update([
                'temp_file_path' => null,
                'temp_expires_at' => null
            ]);
        }
    }

    /**
     * Handle video upload cancelled event
     */
    private function handleVideoUploadCancelled(array $data)
    {
        $uploadId = $data['id'] ?? null;

        if (!$uploadId) {
            Log::warning('Mux webhook: Missing upload ID in video.upload.cancelled');
            return;
        }

        $video = Video::where('mux_upload_id', $uploadId)->first();

        if (!$video) {
            Log::warning('Mux webhook: Video not found for upload ID', ['upload_id' => $uploadId]);
            return;
        }

        $video->update(['status' => 'rejected']);

        Log::info('Video upload cancelled via Mux webhook', [
            'video_id' => $video->id,
            'upload_id' => $uploadId
        ]);

        // Clean up temporary files
        if ($video->temp_file_path) {
            Storage::disk('public')->delete($video->temp_file_path);
            $video->update([
                'temp_file_path' => null,
                'temp_expires_at' => null
            ]);
        }
    }

    /**
     * Handle video asset deleted event
     */
    private function handleVideoAssetDeleted(array $data)
    {
        $assetId = $data['id'] ?? null;

        if (!$assetId) {
            Log::warning('Mux webhook: Missing asset ID in video.asset.deleted');
            return;
        }

        $video = Video::where('mux_asset_id', $assetId)->first();

        if (!$video) {
            Log::warning('Mux webhook: Video not found for asset ID', ['asset_id' => $assetId]);
            return;
        }

        // Clear Mux fields but keep video as rejected
        $video->update([
            'status' => 'rejected',
            'mux_asset_id' => null,
            'mux_playback_id' => null,
            'mux_upload_id' => null,
        ]);

        Log::info('Video asset deleted via Mux webhook', [
            'video_id' => $video->id,
            'asset_id' => $assetId
        ]);
    }
}