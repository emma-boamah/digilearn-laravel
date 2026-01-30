<?php

namespace App\Services;

use App\Enums\LessonLevel;

class LessonDifficultyService
{
    public function getProgressionScore(string $from, string $to): float
    {
        // Normalize strings to match enum cases (e.g., "Primary 1" -> "primary-1")
        $from = strtolower(str_replace(' ', '-', $from));
        $to = strtolower(str_replace(' ', '-', $to));

        // tryFrom returns null if the string is invalid
        $fromLevel = LessonLevel::tryFrom($from);
        $toLevel = LessonLevel::tryFrom($to);

        if (!$fromLevel || !$toLevel) return 0.1;

        $difference = $toLevel->score() - $fromLevel->score();

        return match ($difference) {
            0 => 0.8, // Same level - reinforcement
            1 => 1.0, // Perfect forward progression
            -1 => 0.9, // Revision (one level back)
            2 => 0.7, // Forward leap
            -2 => 0.6, // Backward leap
            default => 0.1,
        };
    }
}
