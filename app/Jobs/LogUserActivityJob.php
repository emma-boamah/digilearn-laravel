<?php

namespace App\Jobs;

use App\Events\UserActivityLogged;
use App\Models\UserActivity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LogUserActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 60; // 1 minute

    private array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Sanitize inputs
            $description = trim($this->data['description']);
            $ipAddress = filter_var($this->data['ip_address'], FILTER_VALIDATE_IP) ? $this->data['ip_address'] : null;

            $activity = UserActivity::create([
                'user_id' => $this->data['user_id'],
                'type' => $this->data['type'],
                'description' => $description,
                'metadata' => $this->data['metadata'],
                'ip_address' => $ipAddress,
                'user_agent' => $this->data['user_agent'],
            ]);

            // Clear relevant caches
            $this->clearActivityCaches($this->data['user_id'], $this->data['type']);

            // Fire the event
            event(new UserActivityLogged($activity));

        } catch (\Exception $e) {
            Log::error('Failed to log user activity via job', [
                'type' => $this->data['type'],
                'description' => $this->data['description'],
                'user_id' => $this->data['user_id'],
                'error' => $e->getMessage()
            ]);

            throw $e; // Re-throw to mark job as failed
        }
    }

    /**
     * Clear activity caches when new activity is logged
     */
    private function clearActivityCaches(?int $userId, ?string $type): void
    {
        Cache::tags(['activities'])->flush();
    }
}