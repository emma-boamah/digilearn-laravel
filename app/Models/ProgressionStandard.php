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
        'individual_level_lesson_threshold',
        'individual_level_quiz_threshold',
        'individual_level_score_threshold',
        'required_number_of_lessons_individual',
        'required_number_of_quizzes_individual',
        'required_number_of_lessons_group',
        'required_number_of_quizzes_group',
        'is_active',
    ];

    protected $casts = [
        'required_lesson_completion_percentage' => 'decimal:2',
        'required_quiz_completion_percentage' => 'decimal:2',
        'required_average_quiz_score' => 'decimal:2',
        'minimum_quiz_score' => 'decimal:2',
        'lesson_watch_threshold_percentage' => 'decimal:2',
        'individual_level_lesson_threshold' => 'decimal:2',
        'individual_level_quiz_threshold' => 'decimal:2',
        'individual_level_score_threshold' => 'decimal:2',
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
            'required_number_of_lessons_individual' => 10,
            'required_number_of_quizzes_individual' => 5,
            'required_number_of_lessons_group' => 20,
            'required_number_of_quizzes_group' => 10,
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
                'individual_level_lesson_threshold',
                'individual_level_quiz_threshold',
                'individual_level_score_threshold',
                'required_number_of_lessons_individual',
                'required_number_of_quizzes_individual',
                'required_number_of_lessons_group',
                'required_number_of_quizzes_group',
            ]);
        }

        return static::getDefaults();
    }
    /**
     * Get all valid level names for a specific level group.
     * Including variations like "Grade 1", "Class 1", etc.
     */
    public static function getLevelsForGroup($levelGroup)
    {
        $mapping = [
            'primary-lower' => [
                'Primary 1', 'Grade 1', 'Class 1', 'Basic 1',
                'Primary 2', 'Grade 2', 'Class 2', 'Basic 2',
                'Primary 3', 'Grade 3', 'Class 3', 'Basic 3'
            ],
            'primary-upper' => [
                'Primary 4', 'Grade 4', 'Class 4', 'Basic 4',
                'Primary 5', 'Grade 5', 'Class 5', 'Basic 5',
                'Primary 6', 'Grade 6', 'Class 6', 'Basic 6'
            ],
            'jhs' => [
                'JHS 1', 'J.H.S 1', 'JHS-1', 'Basic 7', 'Grade 7',
                'JHS 2', 'J.H.S 2', 'JHS-2', 'Basic 8', 'Grade 8',
                'JHS 3', 'J.H.S 3', 'JHS-3', 'Basic 9', 'Grade 9'
            ],
            'shs' => [
                'SHS 1', 'S.H.S 1', 'SHS-1', 'Grade 10',
                'SHS 2', 'S.H.S 2', 'SHS-2', 'Grade 11',
                'SHS 3', 'S.H.S 3', 'SHS-3', 'Grade 12'
            ],
            'tertiary' => ['Tertiary', 'University', 'College', 'Polytechnic'],
        ];

        return $mapping[$levelGroup] ?? [$levelGroup];
    }

    /**
     * Get the level group for a specific individual level.
     * Handles variations and case-insensitivity.
     */
    public static function getLevelGroup($individualLevel)
    {
        // Normalize input
        // $normalized = trim(strtolower($individualLevel ?? '')); 
        // For now, checks containment in the arrays.
        // Optimization: Could build a reverse map cache if performance becomes an issue.

        $groups = ['primary-lower', 'primary-upper', 'jhs', 'shs', 'tertiary'];
        
        foreach ($groups as $group) {
            $levels = static::getLevelsForGroup($group);
            // Case-insensitive check
            foreach ($levels as $level) {
                if (strcasecmp($level, $individualLevel) === 0) {
                    return $group;
                }
            }
        }

        return 'unknown';
    }
}