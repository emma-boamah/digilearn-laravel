<?php

namespace App\Services;

/**
 * Thin wrapper around FFProbe for extracting video duration.
 * Exists primarily so that controllers can have this dependency
 * injected (and therefore mocked in tests) instead of calling
 * the static FFProbe::create() directly.
 */
class VideoDurationService
{
    /**
     * Return the duration of the video at the given absolute path, in seconds.
     */
    public function getDuration(string $absolutePath): float
    {
        $ffprobe = \FFMpeg\FFProbe::create();

        return (float) round($ffprobe->format($absolutePath)->get('duration'), 2);
    }
}
