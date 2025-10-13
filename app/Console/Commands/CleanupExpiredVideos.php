<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CleanupExpiredVideos extends Command
{
    protected $signature = 'videos:cleanup-expired {--dry-run : Show what would be deleted without actually deleting}';
    protected $description = 'Clean up expired temporary video files and update database records';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('🧹 Starting cleanup of expired temporary videos...');

        // Find expired videos
        $expiredVideos = Video::where('temp_expires_at', '<', now())
            ->whereNotNull('temp_file_path')
            ->get();

        if ($expiredVideos->isEmpty()) {
            $this->info('✅ No expired temporary videos found.');
            return 0;
        }

        $this->info("Found {$expiredVideos->count()} expired temporary video(s)");

        if ($dryRun) {
            foreach ($expiredVideos as $video) {
                $this->line("  • {$video->title} - Expired {$video->temp_expires_at->diffForHumans()}");
            }
            $this->warn('🔍 DRY RUN: No files will be deleted.');
            return 0;
        }

        $deletedCount = 0;
        foreach ($expiredVideos as $video) {
            try {
                // Delete the temporary file
                if ($video->temp_file_path && Storage::disk('public')->exists($video->temp_file_path)) {
                    Storage::disk('public')->delete($video->temp_file_path);
                    $this->line("  ✅ Deleted: {$video->temp_file_path}");
                }

                // Update database record
                $video->update([
                    'temp_file_path' => null,
                    'temp_expires_at' => null,
                ]);

                $deletedCount++;

            } catch (\Exception $e) {
                $this->error("  ❌ Failed to delete {$video->title}: {$e->getMessage()}");
                Log::error('Failed to cleanup expired video', [
                    'video_id' => $video->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("🎉 Cleanup completed! Files deleted: {$deletedCount}");
        return 0;
    }
}
