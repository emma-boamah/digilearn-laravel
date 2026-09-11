<?php

namespace App\Jobs;

use App\Models\Textbook;
use App\Models\TextbookChapter;
use App\Services\TextbookExtractionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTextbookContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 600;

    public function __construct(public int $textbookId, public ?int $chapterId = null)
    {
    }

    public function handle(TextbookExtractionService $service): void
    {
        $textbook = Textbook::find($this->textbookId);

        if (!$textbook) {
            Log::warning("ProcessTextbookContentJob: Textbook ID {$this->textbookId} not found, skipping.");
            return;
        }

        $textbook->update(['content_extraction_status' => 'processing']);

        if ($this->chapterId) {
            $chapter = TextbookChapter::find($this->chapterId);
            if ($chapter) {
                $service->extractChapterContent($chapter);
            }
        } else {
            // Process all chapters
            foreach ($textbook->chapters as $chapter) {
                try {
                    $service->extractChapterContent($chapter);
                } catch (\Throwable $e) {
                    Log::warning("ProcessTextbookContentJob: Chapter {$chapter->id} failed: " . $e->getMessage());
                }
            }
        }

        $textbook->update(['content_extraction_status' => 'extracted']);
        Log::info("ProcessTextbookContentJob finished for Textbook ID {$this->textbookId}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessTextbookContentJob failed for ID {$this->textbookId}: " . $exception->getMessage());

        $textbook = Textbook::find($this->textbookId);
        if ($textbook) {
            $textbook->update(['content_extraction_status' => 'failed']);
        }
    }
}
