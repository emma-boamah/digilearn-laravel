<?php

namespace App\Jobs;

use App\Models\Curriculum;
use App\Services\CurriculumExtractionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCurriculumExtractionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 600; // 10 minutes max for large curriculum PDFs

    public function __construct(public int $curriculumId)
    {
    }

    public function handle(CurriculumExtractionService $service): void
    {
        $curriculum = Curriculum::find($this->curriculumId);

        if (!$curriculum) {
            Log::warning("ProcessCurriculumExtractionJob: Curriculum ID {$this->curriculumId} not found, skipping.");
            return;
        }

        Log::info("ProcessCurriculumExtractionJob starting for ID {$this->curriculumId}");
        $service->extractCurriculum($curriculum);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessCurriculumExtractionJob failed permanently for ID {$this->curriculumId}: " . $exception->getMessage());

        $curriculum = Curriculum::find($this->curriculumId);
        if ($curriculum) {
            $curriculum->update([
                'extraction_status' => 'failed',
                'extraction_error' => $exception->getMessage(),
            ]);
        }
    }
}
