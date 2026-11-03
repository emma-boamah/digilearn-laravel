<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;

class ImageOptimizerService
{
    /**
     * Default WebP quality setting (0-100).
     * 80 is the industry standard for optimal visual clarity and minimum file size.
     */
    public const DEFAULT_QUALITY = 80;

    /**
     * Max dimensions by context to minimize mobile data consumption.
     */
    public const MAX_THUMBNAIL_WIDTH = 800;
    public const MAX_THUMBNAIL_HEIGHT = 600;

    public const MAX_HERO_BANNER_WIDTH = 1600;
    public const MAX_HERO_BANNER_HEIGHT = 900;

    public const MAX_AVATAR_DIMENSION = 400; // 400x400 square bounds
    public const MAX_CONTENT_IMAGE_WIDTH = 1200;

    /**
     * Convert and store an uploaded image as WebP.
     *
     * @param UploadedFile|string $file Uploaded file or path to local file
     * @param string $directory Storage subdirectory (e.g. 'thumbnails', 'hero_banners')
     * @param int|null $maxWidth Optional max width constraint
     * @param int|null $maxHeight Optional max height constraint
     * @param int $quality Compression quality (0-100)
     * @param string $disk Storage disk ('public' by default)
     * @param string|null $customFilename Optional custom filename without extension
     * @return string Stored relative path (e.g. 'thumbnails/17300000_abc123.webp')
     * @throws Exception
     */
    public function convertToWebp(
        UploadedFile|string $file,
        string $directory,
        ?int $maxWidth = null,
        ?int $maxHeight = null,
        int $quality = self::DEFAULT_QUALITY,
        string $disk = 'public',
        ?string $customFilename = null
    ): string {
        $realPath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (!$realPath || !file_exists($realPath)) {
            throw new Exception("Source image file does not exist: {$realPath}");
        }

        // Get image data
        $fileContents = file_get_contents($realPath);
        if ($fileContents === false) {
            throw new Exception("Unable to read image content from {$realPath}");
        }

        // Create GD resource from string
        $image = @imagecreatefromstring($fileContents);
        if (!$image) {
            // If GD cannot parse (e.g. SVG or raw video), fallback to default store if UploadedFile
            if ($file instanceof UploadedFile) {
                Log::warning("ImageOptimizerService: Unable to decode image with GD. Falling back to original file.", [
                    'filename' => $file->getClientOriginalName()
                ]);
                return $file->store($directory, $disk);
            }
            throw new Exception("Unable to decode image from path: {$realPath}");
        }

        // Auto-fix mobile camera EXIF orientation if available (typically JPEG)
        $image = $this->fixExifOrientation($realPath, $image);

        // Get dimensions after orientation correction
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        // Calculate target dimensions
        [$targetWidth, $targetHeight] = $this->calculateTargetDimensions(
            $origWidth,
            $origHeight,
            $maxWidth,
            $maxHeight
        );

        // Resize if needed
        if ($targetWidth !== $origWidth || $targetHeight !== $origHeight) {
            $resized = imagecreatetruecolor($targetWidth, $targetHeight);

            // Preserve alpha transparency
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefilledrectangle($resized, 0, 0, $targetWidth, $targetHeight, $transparent);

            imagecopyresampled(
                $resized,
                $image,
                0, 0, 0, 0,
                $targetWidth,
                $targetHeight,
                $origWidth,
                $origHeight
            );

            imagedestroy($image);
            $image = $resized;
        } else {
            // Even if unscaled, ensure alpha channel is preserved
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        // Generate temporary WebP output file
        $tempPath = tempnam(sys_get_temp_dir(), 'webp_opt_');
        $saved = imagewebp($image, $tempPath, max(1, min(100, $quality)));
        imagedestroy($image);

        if (!$saved || !file_exists($tempPath)) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
            throw new Exception("Failed to encode image to WebP format.");
        }

        // Generate filename
        $baseName = $customFilename ?: (time() . '_' . Str::random(12));
        $targetRelativePath = trim($directory, '/') . '/' . $baseName . '.webp';

        // Save to target storage disk
        $stream = fopen($tempPath, 'r');
        Storage::disk($disk)->put($targetRelativePath, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        @unlink($tempPath);

        return $targetRelativePath;
    }

    /**
     * Store a video or lesson thumbnail as WebP (constrained to max 800px width).
     */
    public function storeThumbnail(UploadedFile $file, string $directory = 'thumbnails', ?string $customFilename = null): string
    {
        return $this->convertToWebp(
            $file,
            $directory,
            self::MAX_THUMBNAIL_WIDTH,
            self::MAX_THUMBNAIL_HEIGHT,
            self::DEFAULT_QUALITY,
            'public',
            $customFilename
        );
    }

    /**
     * Store a hero banner image as WebP (constrained to max 1600px width).
     */
    public function storeHeroBanner(UploadedFile $file, string $directory = 'hero_banners', ?string $customFilename = null): string
    {
        return $this->convertToWebp(
            $file,
            $directory,
            self::MAX_HERO_BANNER_WIDTH,
            self::MAX_HERO_BANNER_HEIGHT,
            82, // Slightly higher quality for prominent hero banners
            'public',
            $customFilename
        );
    }

    /**
     * Store a user or admin avatar as WebP (constrained to max 400x400 square bounds).
     */
    public function storeAvatar(UploadedFile $file, string $directory = 'avatars', ?string $customFilename = null): string
    {
        return $this->convertToWebp(
            $file,
            $directory,
            self::MAX_AVATAR_DIMENSION,
            self::MAX_AVATAR_DIMENSION,
            self::DEFAULT_QUALITY,
            'public',
            $customFilename
        );
    }

    /**
     * Store a tutor headshot as WebP (constrained to max 400x400 bounds).
     */
    public function storeHeadshot(UploadedFile $file, string $directory = 'tutors/headshots', ?string $customFilename = null): string
    {
        return $this->convertToWebp(
            $file,
            $directory,
            self::MAX_AVATAR_DIMENSION,
            self::MAX_AVATAR_DIMENSION,
            self::DEFAULT_QUALITY,
            'public',
            $customFilename
        );
    }

    /**
     * Convert an existing local storage image to WebP and return the new path.
     * Deletes the old file if $deleteOriginal is true.
     */
    public function convertExistingStorageFile(
        string $relativeStoragePath,
        ?int $maxWidth = null,
        ?int $maxHeight = null,
        int $quality = self::DEFAULT_QUALITY,
        string $disk = 'public',
        bool $deleteOriginal = false
    ): ?string {
        if (!Storage::disk($disk)->exists($relativeStoragePath)) {
            return null;
        }

        // If already webp, return as-is
        $ext = strtolower(pathinfo($relativeStoragePath, PATHINFO_EXTENSION));
        if ($ext === 'webp') {
            return $relativeStoragePath;
        }

        $fullPath = Storage::disk($disk)->path($relativeStoragePath);
        $directory = dirname($relativeStoragePath);
        $filename = pathinfo($relativeStoragePath, PATHINFO_FILENAME);

        try {
            $newRelativePath = $this->convertToWebp(
                $fullPath,
                $directory,
                $maxWidth,
                $maxHeight,
                $quality,
                $disk,
                $filename . '_' . Str::random(4)
            );

            if ($deleteOriginal && $newRelativePath !== $relativeStoragePath) {
                Storage::disk($disk)->delete($relativeStoragePath);
            }

            return $newRelativePath;
        } catch (Exception $e) {
            Log::error("Failed to convert existing image {$relativeStoragePath} to WebP: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate target dimensions keeping aspect ratio within max bounds.
     */
    private function calculateTargetDimensions(int $width, int $height, ?int $maxWidth, ?int $maxHeight): array
    {
        if ($width <= 0 || $height <= 0) {
            return [max(1, $width), max(1, $height)];
        }

        // If no constraints or already within bounds, return original
        if (!$maxWidth && !$maxHeight) {
            return [$width, $height];
        }

        $maxWidth = $maxWidth ?: $width;
        $maxHeight = $maxHeight ?: $height;

        // If already smaller than max, don't upscale
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return [$width, $height];
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));

        return [$targetWidth, $targetHeight];
    }

    /**
     * Correct image orientation based on mobile camera EXIF metadata.
     */
    private function fixExifOrientation(string $filePath, $image)
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }

        try {
            $exif = @exif_read_data($filePath);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $rotated = imagerotate($image, 180, 0);
                        imagedestroy($image);
                        return $rotated;
                    case 6:
                        $rotated = imagerotate($image, -90, 0);
                        imagedestroy($image);
                        return $rotated;
                    case 8:
                        $rotated = imagerotate($image, 90, 0);
                        imagedestroy($image);
                        return $rotated;
                }
            }
        } catch (\Throwable $e) {
            // Ignore EXIF read errors
        }

        return $image;
    }
}
