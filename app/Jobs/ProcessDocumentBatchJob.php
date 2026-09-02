<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\UploadTask;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessDocumentBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 2;

    protected array $documentIds;
    protected ?string $taskId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $documentIds, ?string $taskId = null)
    {
        $this->documentIds = $documentIds;
        $this->taskId = $taskId;
        $this->onQueue('uploads');
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        $task = $this->taskId ? UploadTask::find($this->taskId) : null;
        $task?->updateProgress(50, 'Processing documents & dispatching AI analysis...');

        $processedCount = 0;
        foreach ($this->documentIds as $docId) {
            $document = Document::find($docId);
            if (!$document) {
                continue;
            }

            try {
                // Dispatch SQ3R AI processing job for the document
                ProcessDocumentSq3rJob::dispatch($document->id)->onQueue('uploads');

                // Send notification
                $notificationService->notifyNewDocument($document);
                $processedCount++;
            } catch (Throwable $e) {
                Log::warning("ProcessDocumentBatchJob: Error queuing SQ3R for document #{$docId}: " . $e->getMessage());
            }
        }

        $task?->markCompleted("{$processedCount} document(s) processed and queued for AI analysis.");
        Log::info("ProcessDocumentBatchJob: Completed processing {$processedCount} documents.");
    }

    /**
     * Handle job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("ProcessDocumentBatchJob permanently failed: " . $exception->getMessage());
        $task = $this->taskId ? UploadTask::find($this->taskId) : null;
        $task?->markFailed($exception->getMessage());
    }
}
