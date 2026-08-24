<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\GeminiSq3rService;
use App\Services\DocumentCognitiveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDocumentSq3rJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 300; // 5 minutes max for lengthy academic PDFs

    protected int $documentId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $documentId)
    {
        $this->documentId = $documentId;
    }

    /**
     * Execute the job.
     */
    public function handle(GeminiSq3rService $sq3rService, DocumentCognitiveService $cognitiveService): void
    {
        $document = Document::find($this->documentId);

        if (!$document) {
            Log::info("ProcessDocumentSq3rJob: Document ID {$this->documentId} not found, skipping.");
            return;
        }

        Log::info("ProcessDocumentSq3rJob: Pre-processing document [{$document->id}] '{$document->title}'");

        try {
            // 1. Run full SQ3R Pipeline
            $sq3rService->processDocument($document);
            Log::info("ProcessDocumentSq3rJob: Successfully synthesized document [{$document->id}] via SQ3R pipeline.");
        } catch (\Throwable $e) {
            Log::warning("ProcessDocumentSq3rJob: SQ3R error for [{$document->id}]: {$e->getMessage()}. Invoking Batch Grasping fallback.");
            try {
                // 2. Fallback to Map-Reduce Batch Grasping
                $cognitiveService->analyzeDocumentContent($document);
                Log::info("ProcessDocumentSq3rJob: Successfully synthesized document [{$document->id}] via Batch Grasping.");
            } catch (\Throwable $cogEx) {
                Log::error("ProcessDocumentSq3rJob: Batch Grasping also failed for [{$document->id}]: {$cogEx->getMessage()}");
            }
        }
    }
}
