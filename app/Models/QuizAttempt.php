<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'quiz_title',
        'quiz_subject',
        'quiz_level',
        'total_questions',
        'correct_answers',
        'incorrect_answers',
        'score_percentage',
        'time_taken_seconds',
        'passed',
        'failed_due_to_violation',
        'attempt_number',
        'answers',
        'question_details',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'failed_due_to_violation' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'answers' => 'array',
        'question_details' => 'array',
        'score_percentage' => 'decimal:2',
    ];

    /**
     * Get the user that owns the attempt.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the quiz that owns the attempt.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the violations for this attempt.
     */
    public function violations()
    {
        return $this->hasMany(QuizViolation::class, 'quiz_attempt_id');
    }

    /**
     * Get the trust score for this attempt.
     */
    public function getTrustScoreAttribute()
    {
        $totalPoints = $this->violations->sum('points');
        return max(0, 100 - $totalPoints);
    }

    /**
     * Get proctoring insights and alerts.
     */
    public function getProctoringInsightsAttribute()
    {
        $alerts = [];
        
        // 1. Speed Alert: If student averages < 5 seconds per question
        if ($this->total_questions > 0 && $this->time_taken_seconds > 0) {
            $secondsPerQuestion = $this->time_taken_seconds / $this->total_questions;
            if ($secondsPerQuestion < 5 && $this->score_percentage >= 80) {
                $alerts[] = [
                    'type' => 'speed',
                    'label' => 'High Speed Alert',
                    'details' => "Finished in " . round($secondsPerQuestion, 1) . "s per question (Typical: 15-30s). Potential script/external help.",
                    'severity' => 'high'
                ];
            }
        }

        // 2. Focus Alert: Check for tab switching duration or frequency
        $tabSwitchCount = $this->violations->whereIn('violation_type', ['tab_switch', 'focus_loss'])->count();
        if ($tabSwitchCount >= 5) {
            $alerts[] = [
                'type' => 'focus',
                'label' => 'Significant Distraction',
                'details' => "Student switched tabs or lost focus {$tabSwitchCount} times during the quiz.",
                'severity' => $tabSwitchCount >= 10 ? 'high' : 'medium'
            ];
        }

        // 3. Session Alert: Multi-device or session conflict
        $sessionAlerts = $this->violations->whereIn('violation_type', ['session_conflict', 'session_hijack'])->count();
        if ($sessionAlerts > 0) {
            $alerts[] = [
                'type' => 'session',
                'label' => 'Session Security Alert',
                'details' => "Multiple session/device conflicts detected during this attempt.",
                'severity' => 'high'
            ];
        }

        return $alerts;
    }

    /**
     * Record a quiz attempt
     */
    public static function recordAttempt($userId, $quizData, $answers, $timeTaken)
    {
        // Get the next attempt number for this user and quiz
        $attemptNumber = static::where('user_id', $userId)
                              ->where('quiz_id', $quizData['id'])
                              ->max('attempt_number') + 1;

        // Calculate score
        $correctAnswers = 0;
        $totalQuestions = count($quizData['questions']);
        
        foreach ($answers as $questionId => $userAnswer) {
            $question = collect($quizData['questions'])->firstWhere('id', $questionId);
            if ($question && $question['correct_answer'] === $userAnswer) {
                $correctAnswers++;
            }
        }

        // Get minimum passing score from standards
        $standards = \App\Models\ProgressionStandard::getStandardsForLevel($quizData['level_group'] ?? 'primary-lower');
        $minimumScore = $standards['minimum_quiz_score'];

        $scorePercentage = ($correctAnswers / $totalQuestions) * 100;
        $passed = $scorePercentage >= $minimumScore;

        return static::create([
            'user_id' => $userId,
            'quiz_id' => $quizData['id'],
            'quiz_title' => $quizData['title'],
            'quiz_subject' => $quizData['subject'],
            'quiz_level' => $quizData['level'],
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'incorrect_answers' => $totalQuestions - $correctAnswers,
            'score_percentage' => $scorePercentage,
            'time_taken_seconds' => $timeTaken,
            'passed' => $passed,
            'attempt_number' => $attemptNumber,
            'answers' => $answers,
            'question_details' => $quizData['questions'],
            'started_at' => now()->subSeconds($timeTaken),
            'completed_at' => now(),
        ]);
    }

    /**
     * Get quiz statistics for a user and level
     */
    public static function getLevelStats($userId, $level)
    {
        return static::where('user_id', $userId)
                    ->where('quiz_level', $level)
                    ->selectRaw('
                        COUNT(DISTINCT quiz_id) as total_quizzes,
                        COUNT(DISTINCT CASE WHEN passed = 1 THEN quiz_id END) as passed_quizzes,
                        AVG(score_percentage) as avg_score,
                        MAX(score_percentage) as best_score,
                        COUNT(*) as total_attempts,
                        SUM(time_taken_seconds) as total_time
                    ')
                    ->first();
    }

    /**
     * Get quiz statistics for a user and level group
     */
    public static function getLevelGroupStats($userId, $levelGroup)
    {
        // Use centralized level mapping
        $levels = ProgressionStandard::getLevelsForGroup($levelGroup);

        return static::where('user_id', $userId)
                    ->whereIn('quiz_level', $levels)
                    ->selectRaw('
                        COUNT(DISTINCT quiz_id) as total_quizzes,
                        COUNT(DISTINCT CASE WHEN passed = 1 THEN quiz_id END) as passed_quizzes,
                        AVG(score_percentage) as avg_score,
                        MAX(score_percentage) as best_score,
                        COUNT(*) as total_attempts,
                        SUM(time_taken_seconds) as total_time
                    ')
                    ->first();
    }

    /**
     * Get best attempt for each quiz
     */
    public static function getBestAttempts($userId, $level)
    {
        return static::where('user_id', $userId)
                    ->where('quiz_level', $level)
                    ->selectRaw('quiz_id, MAX(score_percentage) as best_score, passed')
                    ->groupBy('quiz_id')
                    ->get();
    }
}
