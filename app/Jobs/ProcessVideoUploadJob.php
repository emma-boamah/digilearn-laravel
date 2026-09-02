<?php

namespace App\Jobs;

use App\Models\UploadTask;
use App\Models\Video;
use App\Services\NotificationService;
use App\Services\VideoDurationService;
use App\Services\VimeoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessVideoUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800; // 30 minutes for large Vimeo uploads
    public $tries = 2;

    protected int $videoId;
    protected ?string $taskId;
    protected ?string $uploadDestination;

    /**
     * Create a new job instance.
     */
    public function __construct(int $videoId, ?string $taskId = null, ?string $uploadDestination = null)
    {
        $this->videoId = $videoId;
        $this->taskId = $taskId;
        $this->uploadDestination = $uploadDestination;
        $this->onQueue('uploads');
    }

    /**
     * Execute the job.
     */
    public function handle(
        VideoDurationService $durationService,
        NotificationService $notificationService
    ): void {
        $video = Video::find($this->videoId);
        $task = $this->taskId ? UploadTask::find($this->taskId) : null;

        if (!$video) {
            Log::warning("ProcessVideoUploadJob: Video ID {$this->videoId} not found.");
            $task?->markFailed("Video record #{$this->videoId} not found.");
            return;
        }

        try {
            $task?->updateProgress(10, 'Processing video metadata & duration...');

            // Step 1: Calculate duration and file size if video file exists on disk
            $tempPath = $video->temp_file_path;
            if ($tempPath && Storage::disk('public')->exists($tempPath)) {
                $fullPath = Storage::disk('public')->path($tempPath);
                $fileSize = Storage::disk('public')->size($tempPath);

                $durationSeconds = $durationService->getDuration($fullPath);

                $video->duration_seconds = $durationSeconds ?: $video->duration_seconds;
                $video->file_size_bytes = $fileSize ?: $video->file_size_bytes;
                $video->save();

                Log::info("ProcessVideoUploadJob: Video #{$video->id} duration: {$durationSeconds}s, size: {$fileSize} bytes");
            }

            // Step 2: Handle destination-specific processing (e.g. Vimeo)
            $destination = $this->uploadDestination ?? $video->video_source;

            if ($destination === 'vimeo' && $tempPath && Storage::disk('public')->exists($tempPath)) {
                $task?->updateProgress(30, 'Uploading video to Vimeo cloud...');
                Log::info("ProcessVideoUploadJob: Uploading video #{$video->id} to Vimeo");

                $vimeoService = app(VimeoService::class);
                $uploadId = 'video_' . $video->id . '_' . time();
                $result = $vimeoService->uploadVideo(
                    $tempPath,
                    $video->title,
                    $video->description,
                    $video->uploaded_by,
                    $uploadId
                );

                if ($result && is_array($result) && ($result['success'] ?? false)) {
                    $video->update([
                        'vimeo_id' => $result['video_id'] ?? null,
                        'vimeo_embed_url' => $result['embed_url'] ?? null,
                        'video_source' => 'vimeo',
                        'status' => 'approved',
                        'temp_file_path' => null,
                        'temp_expires_at' => null,
                    ]);

                    // Clean up local temp file
                    Storage::disk('public')->delete($tempPath);
                    Log::info("ProcessVideoUploadJob: Successfully uploaded video #{$video->id} to Vimeo (ID: " . ($result['video_id'] ?? '') . ")");
                } else {
                    $errorMsg = is_array($result) ? ($result['error'] ?? 'Unknown Vimeo error') : 'Vimeo upload failed';
                    throw new \Exception("Failed to upload to Vimeo: {$errorMsg}");
                }
            } else {
                // Local, YouTube, Mux, or already processed
                if ($video->status === 'processing') {
                    $video->status = 'approved';
                    $video->save();
                }
            }

            // Step 3: Trigger notifications
            $notificationService->notifyNewVideo($video);

            // Step 4: Mark task completed
            $task?->markCompleted('Video uploaded and processed successfully!');
            Log::info("ProcessVideoUploadJob: Finished processing video #{$video->id}");

        } catch (Throwable $e) {
            Log::error("ProcessVideoUploadJob failed for video #{$this->videoId}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $video->update(['status' => 'rejected']);
            $task?->markFailed($e->getMessage());

            throw $e;
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("ProcessVideoUploadJob permanently failed for video #{$this->videoId}: " . $exception->getMessage());
        $task = $this->taskId ? UploadTask::find($this->taskId) : null;
        $task?->markFailed($exception->getMessage());
    }
}
