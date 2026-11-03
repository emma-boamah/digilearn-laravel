<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Video;
use App\Models\Course;
use App\Models\HeroBanner;
use App\Models\User;
use App\Models\TutorProfile;
use App\Models\SavedLesson;
use App\Services\ImageOptimizerService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ConvertImagesToWebpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:convert-to-webp 
                            {--dry-run : Preview images to convert without writing files or changing the database}
                            {--delete-original : Delete the original JPG/PNG files after successful conversion}
                            {--type= : Process only a specific type: videos, courses, hero-banners, avatars, tutors}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retroactively optimize existing platform images (thumbnails, banners, avatars) to lightweight WebP format';

    protected ImageOptimizerService $imageOptimizer;

    public function __construct(ImageOptimizerService $imageOptimizer)
    {
        parent::__construct();
        $this->imageOptimizer = $imageOptimizer;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $deleteOriginal = (bool) $this->option('delete-original');
        $targetType = $this->option('type') ? strtolower((string) $this->option('type')) : null;

        if ($dryRun) {
            $this->warn('--- DRY RUN MODE (No files or database records will be modified) ---');
        }

        $totalOriginalBytes = 0;
        $totalNewBytes = 0;
        $convertedCount = 0;
        $skippedCount = 0;

        $typesToProcess = match ($targetType) {
            'videos' => ['videos'],
            'courses' => ['courses'],
            'hero-banners', 'banners' => ['hero-banners'],
            'avatars', 'users' => ['avatars'],
            'tutors' => ['tutors'],
            null => ['videos', 'courses', 'hero-banners', 'avatars', 'tutors'],
            default => null,
        };

        if ($typesToProcess === null) {
            $this->error("Invalid --type '{$targetType}'. Valid types: videos, courses, hero-banners, avatars, tutors");
            return 1;
        }

        foreach ($typesToProcess as $type) {
            $this->info("\nProcessing [{$type}]...");

            match ($type) {
                'videos' => $this->processVideos($dryRun, $deleteOriginal, $convertedCount, $skippedCount, $totalOriginalBytes, $totalNewBytes),
                'courses' => $this->processCourses($dryRun, $deleteOriginal, $convertedCount, $skippedCount, $totalOriginalBytes, $totalNewBytes),
                'hero-banners' => $this->processHeroBanners($dryRun, $deleteOriginal, $convertedCount, $skippedCount, $totalOriginalBytes, $totalNewBytes),
                'avatars' => $this->processAvatars($dryRun, $deleteOriginal, $convertedCount, $skippedCount, $totalOriginalBytes, $totalNewBytes),
                'tutors' => $this->processTutors($dryRun, $deleteOriginal, $convertedCount, $skippedCount, $totalOriginalBytes, $totalNewBytes),
            };
        }

        $this->newLine();
        $this->info("Conversion Summary:");
        $this->line("  Converted images: {$convertedCount}");
        $this->line("  Skipped images:   {$skippedCount}");

        if ($totalOriginalBytes > 0 && !$dryRun) {
            $savedBytes = max(0, $totalOriginalBytes - $totalNewBytes);
            $reductionPct = round(($savedBytes / $totalOriginalBytes) * 100, 1);
            $this->info(sprintf(
                "  Data saved: %s -> %s (Reduced by %s / %s%%)",
                $this->formatBytes($totalOriginalBytes),
                $this->formatBytes($totalNewBytes),
                $this->formatBytes($savedBytes),
                $reductionPct
            ));
        }

        return 0;
    }

    protected function processVideos(bool $dryRun, bool $deleteOriginal, int &$converted, int &$skipped, int &$origBytes, int &$newBytes): void
    {
        $videos = Video::whereNotNull('thumbnail_path')->get();

        foreach ($videos as $video) {
            $path = $video->thumbnail_path;
            if ($this->shouldSkip($path)) {
                $skipped++;
                continue;
            }

            $disk = 'public';
            if (!Storage::disk($disk)->exists($path)) {
                $this->warn("  Video ID {$video->id}: File missing on disk ({$path})");
                $skipped++;
                continue;
            }

            $oldSize = Storage::disk($disk)->size($path);
            $this->line("  Video ID {$video->id}: {$path} ({$this->formatBytes($oldSize)})");

            if ($dryRun) {
                $converted++;
                continue;
            }

            $newPath = $this->imageOptimizer->convertExistingStorageFile(
                $path,
                ImageOptimizerService::MAX_THUMBNAIL_WIDTH,
                ImageOptimizerService::MAX_THUMBNAIL_HEIGHT,
                80,
                $disk,
                $deleteOriginal
            );

            if ($newPath && $newPath !== $path) {
                $newSize = Storage::disk($disk)->size($newPath);
                $origBytes += $oldSize;
                $newBytes += $newSize;
                $converted++;

                // Update Video record
                $video->thumbnail_path = $newPath;
                $video->save();

                // Also update any SavedLesson references matching old path
                SavedLesson::where('lesson_thumbnail', 'like', "%{$path}%")
                    ->get()
                    ->each(function ($saved) use ($path, $newPath) {
                        $saved->lesson_thumbnail = str_replace($path, $newPath, $saved->lesson_thumbnail);
                        $saved->save();
                    });

                $this->info("    → Converted to {$newPath} ({$this->formatBytes($newSize)})");
            } else {
                $skipped++;
            }
        }
    }

    protected function processCourses(bool $dryRun, bool $deleteOriginal, int &$converted, int &$skipped, int &$origBytes, int &$newBytes): void
    {
        $courses = Course::whereNotNull('thumbnail_path')->get();

        foreach ($courses as $course) {
            $path = $course->thumbnail_path;
            if ($this->shouldSkip($path)) {
                $skipped++;
                continue;
            }

            $disk = 'public';
            if (!Storage::disk($disk)->exists($path)) {
                $this->warn("  Course ID {$course->id}: File missing on disk ({$path})");
                $skipped++;
                continue;
            }

            $oldSize = Storage::disk($disk)->size($path);
            $this->line("  Course ID {$course->id}: {$path} ({$this->formatBytes($oldSize)})");

            if ($dryRun) {
                $converted++;
                continue;
            }

            $newPath = $this->imageOptimizer->convertExistingStorageFile(
                $path,
                ImageOptimizerService::MAX_THUMBNAIL_WIDTH,
                ImageOptimizerService::MAX_THUMBNAIL_HEIGHT,
                80,
                $disk,
                $deleteOriginal
            );

            if ($newPath && $newPath !== $path) {
                $newSize = Storage::disk($disk)->size($newPath);
                $origBytes += $oldSize;
                $newBytes += $newSize;
                $converted++;

                $course->thumbnail_path = $newPath;
                $course->save();

                $this->info("    → Converted to {$newPath} ({$this->formatBytes($newSize)})");
            } else {
                $skipped++;
            }
        }
    }

    protected function processHeroBanners(bool $dryRun, bool $deleteOriginal, int &$converted, int &$skipped, int &$origBytes, int &$newBytes): void
    {
        $banners = HeroBanner::where('media_type', 'image')->whereNotNull('media_path')->get();

        foreach ($banners as $banner) {
            $path = $banner->media_path;
            if ($this->shouldSkip($path)) {
                $skipped++;
                continue;
            }

            $disk = 'public';
            if (!Storage::disk($disk)->exists($path)) {
                $this->warn("  HeroBanner ID {$banner->id}: File missing on disk ({$path})");
                $skipped++;
                continue;
            }

            $oldSize = Storage::disk($disk)->size($path);
            $this->line("  HeroBanner ID {$banner->id}: {$path} ({$this->formatBytes($oldSize)})");

            if ($dryRun) {
                $converted++;
                continue;
            }

            $newPath = $this->imageOptimizer->convertExistingStorageFile(
                $path,
                ImageOptimizerService::MAX_HERO_BANNER_WIDTH,
                ImageOptimizerService::MAX_HERO_BANNER_HEIGHT,
                82,
                $disk,
                $deleteOriginal
            );

            if ($newPath && $newPath !== $path) {
                $newSize = Storage::disk($disk)->size($newPath);
                $origBytes += $oldSize;
                $newBytes += $newSize;
                $converted++;

                $banner->media_path = $newPath;
                $banner->save();

                $this->info("    → Converted to {$newPath} ({$this->formatBytes($newSize)})");
            } else {
                $skipped++;
            }
        }
    }

    protected function processAvatars(bool $dryRun, bool $deleteOriginal, int &$converted, int &$skipped, int &$origBytes, int &$newBytes): void
    {
        $users = User::whereNotNull('avatar')->get();

        foreach ($users as $user) {
            $path = $user->avatar;
            if ($this->shouldSkip($path)) {
                $skipped++;
                continue;
            }

            $disk = 'public';
            if (!Storage::disk($disk)->exists($path)) {
                $skipped++;
                continue;
            }

            $oldSize = Storage::disk($disk)->size($path);
            $this->line("  User ID {$user->id}: {$path} ({$this->formatBytes($oldSize)})");

            if ($dryRun) {
                $converted++;
                continue;
            }

            $newPath = $this->imageOptimizer->convertExistingStorageFile(
                $path,
                ImageOptimizerService::MAX_AVATAR_DIMENSION,
                ImageOptimizerService::MAX_AVATAR_DIMENSION,
                80,
                $disk,
                $deleteOriginal
            );

            if ($newPath && $newPath !== $path) {
                $newSize = Storage::disk($disk)->size($newPath);
                $origBytes += $oldSize;
                $newBytes += $newSize;
                $converted++;

                $user->avatar = $newPath;
                $user->save();

                $this->info("    → Converted to {$newPath} ({$this->formatBytes($newSize)})");
            } else {
                $skipped++;
            }
        }
    }

    protected function processTutors(bool $dryRun, bool $deleteOriginal, int &$converted, int &$skipped, int &$origBytes, int &$newBytes): void
    {
        $tutors = TutorProfile::whereNotNull('headshot_path')->get();

        foreach ($tutors as $tutor) {
            $path = $tutor->headshot_path;
            if ($this->shouldSkip($path)) {
                $skipped++;
                continue;
            }

            $disk = 'public';
            if (!Storage::disk($disk)->exists($path)) {
                $this->warn("  TutorProfile ID {$tutor->id}: File missing on disk ({$path})");
                $skipped++;
                continue;
            }

            $oldSize = Storage::disk($disk)->size($path);
            $this->line("  TutorProfile ID {$tutor->id}: {$path} ({$this->formatBytes($oldSize)})");

            if ($dryRun) {
                $converted++;
                continue;
            }

            $newPath = $this->imageOptimizer->convertExistingStorageFile(
                $path,
                ImageOptimizerService::MAX_AVATAR_DIMENSION,
                ImageOptimizerService::MAX_AVATAR_DIMENSION,
                80,
                $disk,
                $deleteOriginal
            );

            if ($newPath && $newPath !== $path) {
                $newSize = Storage::disk($disk)->size($newPath);
                $origBytes += $oldSize;
                $newBytes += $newSize;
                $converted++;

                $tutor->headshot_path = $newPath;
                $tutor->save();

                $this->info("    → Converted to {$newPath} ({$this->formatBytes($newSize)})");
            } else {
                $skipped++;
            }
        }
    }

    protected function shouldSkip(?string $path): bool
    {
        if (empty($path)) {
            return true;
        }

        // Skip external URLs
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return true;
        }

        // Skip if already webp
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'webp') {
            return true;
        }

        return false;
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
