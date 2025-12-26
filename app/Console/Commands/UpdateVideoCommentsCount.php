<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Video;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;

class UpdateVideoCommentsCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'videos:update-comments-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update comments_count for all videos based on existing comments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating comments count for all videos...');

        // Get comment counts per video
        $commentCounts = Comment::select('video_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('video_id')
            ->groupBy('video_id')
            ->get();

        $updated = 0;
        foreach ($commentCounts as $count) {
            Video::where('id', $count->video_id)
                ->update(['comments_count' => $count->count]);
            $updated++;
        }

        // Reset comments_count to 0 for videos with no comments
        $videosWithNoComments = Video::whereNotIn('id', $commentCounts->pluck('video_id'))
            ->update(['comments_count' => 0]);

        $this->info("Updated comments count for {$updated} videos with comments.");
        $this->info("Reset comments count to 0 for {$videosWithNoComments} videos with no comments.");

        $this->info('Comments count update completed!');
    }
}
