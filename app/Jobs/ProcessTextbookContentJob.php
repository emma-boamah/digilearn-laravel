<?php

namespace App\Jobs;

use App\Models\Textbook;
use App\Models\TextbookChapter;
use App\Models\User;
use App\Notifications\AdminNotification;
use App\Services\TextbookExtractionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

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

        try {
            if ($this->chapterId) {
                $chapter = TextbookChapter::find($this->chapterId);
                if ($chapter) {
                    $service->extractChapterContent($chapter);
                }
            } else {
                // Process all chapters
                foreach ($textbook->chapters as $chapter) {
                    $service->extractChapterContent($chapter);
                }
            }

            $textbook->update(['content_extraction_status' => 'extracted']);
            Log::info("ProcessTextbookContentJob finished for Textbook ID {$this->textbookId}");
            $this->notifySuccess($textbook);

        } catch (\Throwable $e) {
            $parsed = $service->parseGeminiError($e->getMessage());

            if (($parsed['type'] ?? '') === 'token_exhausted') {
                Log::warning("ProcessTextbookContentJob: Credits depleted for ID {$this->textbookId}.");
                $textbook->update([
                    'content_extraction_status' => 'failed',
                    'extraction_error' => json_encode($parsed),
                ]);
                $this->notifyFailure($textbook, $parsed);
                return;
            }

            if ($this->attempts() >= $this->tries) {
                $this->notifyFailure($textbook, $parsed);
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessTextbookContentJob failed for ID {$this->textbookId}: " . $exception->getMessage());

        $textbook = Textbook::find($this->textbookId);
        if ($textbook) {
            $service = app(TextbookExtractionService::class);
            $parsed = $service->parseGeminiError($exception->getMessage());

            $textbook->update([
                'content_extraction_status' => 'failed',
                'extraction_error' => json_encode($parsed),
            ]);

            $this->notifyFailure($textbook, $parsed);
        }
    }

    protected function notifySuccess(Textbook $textbook): void
    {
        $recipients = $this->getRecipients($textbook);
        if ($recipients->isEmpty()) {
            return;
        }

        $title = 'Lesson Extraction Completed';
        $message = "Lessons for \"{$textbook->title}\" were successfully extracted and generated.";
        $url = route('admin.textbooks.show', $textbook);

        try {
            Notification::sendNow($recipients, new AdminNotification($title, $message, $url));
        } catch (\Throwable $e) {
            Log::error("Failed to send content success notification: " . $e->getMessage());
        }
    }

    protected function notifyFailure(Textbook $textbook, array $parsed): void
    {
        $recipients = $this->getRecipients($textbook);
        if ($recipients->isEmpty()) {
            return;
        }

        if (($parsed['type'] ?? '') === 'token_exhausted') {
            $title = 'Gemini Credits Depleted (HTTP 429)';
            $message = 'Google Gemini credits are depleted. Please top up in Google AI Studio.';
        } else {
            $title = 'Lesson Extraction Failed';
            $message = "Lesson extraction for \"{$textbook->title}\" failed: " . ($parsed['message'] ?? 'Unknown error occurred.');
        }

        $url = route('admin.textbooks.show', $textbook);

        try {
            Notification::sendNow($recipients, new AdminNotification($title, $message, $url));
        } catch (\Throwable $e) {
            Log::error("Failed to send content failure notification: " . $e->getMessage());
        }
    }

    protected function getRecipients(Textbook $textbook): \Illuminate\Support\Collection
    {
        $recipients = collect();

        if ($textbook->uploaded_by && $textbook->uploader) {
            $recipients->push($textbook->uploader);
        }

        $admins = User::where('is_admin', true)->get();
        return $recipients->merge($admins)->unique('id');
    }
}

