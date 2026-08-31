<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDocumentSq3rJob;
use App\Models\Document;
use App\Models\Sq3rAnalysis;
use Illuminate\Console\Command;

class ProcessExistingDocumentsSq3rCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:process-sq3r {--force : Re-process documents that already have completed analyses} {--limit= : Limit the number of documents to queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue background SQ3R cognitive analysis for existing documents';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');
        $limit = $this->option('limit');

        $query = Document::query();

        if (!$force) {
            // Find documents that do NOT have a completed analysis
            $query->whereDoesntHave('sq3rAnalysis', function ($q) {
                $q->where('status', 'completed')
                  ->whereNotNull('structured_payload');
            });
        }

        if ($limit && is_numeric($limit)) {
            $query->limit((int) $limit);
        }

        $documents = $query->get();
        $total = $documents->count();

        if ($total === 0) {
            $this->info('✅ All existing documents have already been processed and are ready!');
            return Command::SUCCESS;
        }

        $this->info("🚀 Found {$total} document(s) to queue for background SQ3R processing...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($documents as $doc) {
            ProcessDocumentSq3rJob::dispatch($doc->id);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("🎉 Successfully queued {$total} document(s) to Supervisor background workers!");
        $this->info("You can check progress via supervisor or the 'sq3r_analyses' database table.");

        return Command::SUCCESS;
    }
}
