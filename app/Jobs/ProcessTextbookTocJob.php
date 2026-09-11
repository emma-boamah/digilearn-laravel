<?php

namespace App\Jobs;

use App\Models\Textbook;
use App\Services\TextbookExtractionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTextbookTocJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct(public int $textbookId)
    {
    }

    public function handle(TextbookExtractionService $service): void
    {
        $textbook = Textbook::find($this->textbookId);

        if (!$textbook) {
            Log::warning("ProcessTextbookTocJob: Textbook ID {$this->textbookId} not found, skipping.");
            return;
        }

        Log::info("ProcessTextbookTocJob starting for ID {$this->textbookId}");
        $service->extractTableOfContents($textbook);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessTextbookTocJob failed permanently for ID {$this->textbookId}: " . $exception->getMessage());

        $textbook = Textbook::find($this->textbookId);
        if ($textbook) {
            $textbook->update([
                'toc_extraction_status' => 'failed',
            ]);
        }
    }
}
