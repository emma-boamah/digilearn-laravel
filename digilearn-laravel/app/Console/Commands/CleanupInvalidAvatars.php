<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CleanupInvalidAvatars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-invalid-avatars {--dry-run : Show what would be cleaned without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up invalid avatar paths in the database and remove orphaned files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $this->info($dryRun ? 'DRY RUN: Showing what would be cleaned...' : 'Cleaning up invalid avatars...');

        // Find users with invalid avatar paths
        $users = User::whereNotNull('avatar')->get();
        $invalidCount = 0;
        $orphanedFiles = [];

        foreach ($users as $user) {
            $avatar = $user->avatar;

            // Check for invalid paths
            if (preg_match('/^tmp\//', $avatar) || !preg_match('/^avatars\//', $avatar)) {
                $invalidCount++;
                $this->warn("User {$user->id} ({$user->email}): Invalid avatar path '{$avatar}'");

                if (!$dryRun) {
                    $user->avatar = null;
                    $user->save();
                    $this->info("  → Cleared invalid avatar path");
                }
            }
            // Check for missing files
            elseif (!preg_match('/^https?:\/\//', $avatar) && !Storage::disk('public')->exists($avatar)) {
                $orphanedFiles[] = $avatar;
                $this->warn("User {$user->id} ({$user->email}): Avatar file missing '{$avatar}'");

                if (!$dryRun) {
                    $user->avatar = null;
                    $user->save();
                    $this->info("  → Cleared missing avatar file reference");
                }
            }
        }

        // Check for orphaned files in avatars directory
        $avatarFiles = Storage::disk('public')->files('avatars');
        $validPaths = User::whereNotNull('avatar')
            ->where('avatar', 'like', 'avatars/%')
            ->pluck('avatar')
            ->toArray();

        $orphanedFilesInStorage = [];
        foreach ($avatarFiles as $file) {
            if (!in_array($file, $validPaths)) {
                $orphanedFilesInStorage[] = $file;
                $this->warn("Orphaned file in storage: {$file}");

                if (!$dryRun) {
                    Storage::disk('public')->delete($file);
                    $this->info("  → Deleted orphaned file");
                }
            }
        }

        // Summary
        $this->info("\nSummary:");
        $this->info("  Invalid avatar paths found: {$invalidCount}");
        $this->info("  Orphaned database references: " . count($orphanedFiles));
        $this->info("  Orphaned files in storage: " . count($orphanedFilesInStorage));

        if ($dryRun) {
            $this->info("\nRun without --dry-run to apply these changes.");
        } else {
            Log::info('Avatar cleanup completed', [
                'invalid_paths_cleared' => $invalidCount,
                'orphaned_references_cleared' => count($orphanedFiles),
                'orphaned_files_deleted' => count($orphanedFilesInStorage)
            ]);
        }

        return 0;
    }
}
