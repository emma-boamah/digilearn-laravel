<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ActivityLogCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-log:cleanup {--days=30 : Number of days to retain logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old activity logs from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        $this->info("Cleaning up activity logs older than {$days} days ({$cutoff->toDateString()})...");

        try {
            $count = ActivityLog::where('created_at', '<', $cutoff)->delete();

            $this->info("Successfully deleted {$count} old activity log entries.");
            
            Log::info("Activity logs cleaned up", [
                'days_retained' => $days,
                'deleted_count' => $count,
                'cutoff_date' => $cutoff->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            $this->error("Failed to clean up activity logs: " . $e->getMessage());
            Log::error("Activity log cleanup failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }
}
