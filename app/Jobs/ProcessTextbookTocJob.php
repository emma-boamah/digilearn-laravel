<?php

namespace App\Jobs;

use App\Models\Textbook;
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

        try {
            $service->extractTableOfContents($textbook);
            $this->notifySuccess($textbook);
        } catch (\Throwable $e) {
            $parsed = $service->parseGeminiError($e->getMessage());

            // If Gemini quota/credits are depleted (HTTP 429), fail immediately without useless retries
            if (($parsed['type'] ?? '') === 'token_exhausted') {
                Log::warning("ProcessTextbookTocJob: Credits depleted for ID {$this->textbookId}. Marking failed immediately.");
                $textbook->update([
                    'toc_extraction_status' => 'failed',
                    'extraction_error' => json_encode($parsed),
                ]);
                $this->notifyFailure($textbook, $parsed);
                return;
            }

            // If last attempt, notify failure
            if ($this->attempts() >= $this->tries) {
                $this->notifyFailure($textbook, $parsed);
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessTextbookTocJob failed permanently for ID {$this->textbookId}: " . $exception->getMessage());

        $textbook = Textbook::find($this->textbookId);
        if ($textbook) {
            $service = app(TextbookExtractionService::class);
            $parsed = $service->parseGeminiError($exception->getMessage());

            $textbook->update([
                'toc_extraction_status' => 'failed',
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

        $title = 'TOC Extraction Completed';
        $message = "Table of Contents extraction for \"{$textbook->title}\" has completed successfully.";
        $url = route('admin.textbooks.show', $textbook);

        try {
            Notification::sendNow($recipients, new AdminNotification($title, $message, $url));
        } catch (\Throwable $e) {
            Log::error("Failed to send TOC success notification: " . $e->getMessage());
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
            $title = 'Textbook TOC Extraction Failed';
            $message = "TOC extraction for \"{$textbook->title}\" failed: " . ($parsed['message'] ?? 'Unknown error occurred.');
        }

        $url = route('admin.textbooks.show', $textbook);

        try {
            Notification::sendNow($recipients, new AdminNotification($title, $message, $url));
        } catch (\Throwable $e) {
            Log::error("Failed to send TOC failure notification: " . $e->getMessage());
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

