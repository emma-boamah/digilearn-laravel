<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Curriculum;
use App\Services\CurriculumExtractionService;

class ExtractCurriculumCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'curriculum:extract 
                            {id? : The ID of the curriculum to extract}
                            {--all : Extract all curricula with PDF files}
                            {--mode=local : Extraction mode: local (deterministic coordinate engine) or ai}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract curriculum strands, sub-strands, and learning indicators from PDF with high precision';

    /**
     * Execute the console command.
     */
    public function handle(CurriculumExtractionService $service): int
    {
        $id = $this->argument('id');
        $all = $this->option('all');
        $mode = $this->option('mode') ?: 'local';

        $query = Curriculum::query();

        if ($id) {
            $query->where('id', $id);
        } elseif (!$all) {
            $this->info("Specify an ID or use --all to extract all curricula.");
            $curricula = Curriculum::select('id', 'title', 'file_path', 'extraction_status')->get();
            $this->table(
                ['ID', 'Title', 'File Path', 'Status'],
                $curricula->map(fn($c) => [$c->id, $c->title, $c->file_path, $c->extraction_status])
            );
            return 0;
        }

        $list = $query->get();

        if ($list->isEmpty()) {
            $this->error("No curricula found matching criteria.");
            return 1;
        }

        $this->info("Starting extraction for " . $list->count() . " curriculum document(s) [Mode: {$mode}]...");

        foreach ($list as $curriculum) {
            $this->line("\n------------------------------------------------------------");
            $this->info("Processing [ID: {$curriculum->id}] {$curriculum->title}");
            $this->line("File: {$curriculum->file_path}");

            $t0 = microtime(true);
            try {
                $result = $service->extractCurriculum($curriculum, $mode);
                $elapsed = round(microtime(true) - $t0, 2);

                $strandsCount = count($result['strands'] ?? []);
                $totalInd = 0;
                foreach ($result['strands'] ?? [] as $st) {
                    foreach ($st['sub_strands'] ?? [] as $sub) {
                        $totalInd += count($sub['indicators'] ?? []);
                    }
                }

                $this->info("SUCCESS in {$elapsed}s! Extracted {$strandsCount} strands and {$totalInd} indicators.");

                // Show breakdown by strand
                $rows = [];
                foreach ($result['strands'] ?? [] as $st) {
                    $subCount = count($st['sub_strands'] ?? []);
                    $indCount = 0;
                    foreach ($st['sub_strands'] ?? [] as $sub) {
                        $indCount += count($sub['indicators'] ?? []);
                    }
                    $rows[] = [$st['grade_label'] ?? '-', $st['title'], $subCount, $indCount];
                }
                $this->table(['Grade', 'Strand Title', 'Sub-strands', 'Indicators'], $rows);

            } catch (\Throwable $e) {
                $this->error("FAILED: " . $e->getMessage());
            }
        }

        $this->info("\nCurriculum extraction completed.");
        return 0;
    }
}
