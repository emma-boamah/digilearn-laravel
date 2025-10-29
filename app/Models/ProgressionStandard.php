<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressionStandard extends Model
{
    use HasFactory;

    protected $fillable = [
        'level_group',
        'required_lesson_completion_percentage',
        'required_quiz_completion_percentage',
        'required_average_quiz_score',
        'minimum_quiz_score',
        'lesson_watch_threshold_percentage',
        'is_active',
    ];

    protected $casts = [
        'required_lesson_completion_percentage' => 'decimal:2',
        'required_quiz_completion_percentage' => 'decimal:2',
        'required_average_quiz_score' => 'decimal:2',
        'minimum_quiz_score' => 'decimal:2',
        'lesson_watch_threshold_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the active standard for a level group
     */
    public static function getActiveForLevel($levelGroup)
    {
        return static::where('level_group', $levelGroup)
                    ->where('is_active', true)
                    ->first();
    }

    /**
     * Get default standards if none exist
     */
    public static function getDefaults()
    {
        return [
            'required_lesson_completion_percentage' => 80.00,
            'required_quiz_completion_percentage' => 70.00,
            'required_average_quiz_score' => 70.00,
            'minimum_quiz_score' => 70.00,
            'lesson_watch_threshold_percentage' => 90.00,
            // Individual level progression thresholds (within level groups)
            'individual_level_lesson_threshold' => 75.00, // 75% for within-group progression
            'individual_level_quiz_threshold' => 60.00,    // 60% for within-group progression
            'individual_level_score_threshold' => 65.00,   // 65% average score for within-group
        ];
    }

    /**
     * Get standards for a level group, with defaults as fallback
     */
    public static function getStandardsForLevel($levelGroup)
    {
        $standard = static::getActiveForLevel($levelGroup);

        if ($standard) {
            return $standard->only([
                'required_lesson_completion_percentage',
                'required_quiz_completion_percentage',
                'required_average_quiz_score',
                'minimum_quiz_score',
                'lesson_watch_threshold_percentage',
            ]);
        }

        return static::getDefaults();
    }
}