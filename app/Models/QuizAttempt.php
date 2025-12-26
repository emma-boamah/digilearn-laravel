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
                        COUNT(*) as total_attempts
                    ')
                    ->first();
    }

    /**
     * Get quiz statistics for a user and level group
     */
    public static function getLevelGroupStats($userId, $levelGroup)
    {
        // Map level group to individual levels
        $levelMapping = [
            'primary-lower' => ['Primary 1', 'Primary 2', 'Primary 3'],
            'primary-upper' => ['Primary 4', 'Primary 5', 'Primary 6'],
            'jhs' => ['JHS 1', 'JHS 2', 'JHS 3'],
            'shs' => ['SHS 1', 'SHS 2', 'SHS 3'],
            'tertiary' => ['Tertiary'],
        ];

        $levels = $levelMapping[$levelGroup] ?? [$levelGroup];

        return static::where('user_id', $userId)
                    ->whereIn('quiz_level', $levels)
                    ->selectRaw('
                        COUNT(DISTINCT quiz_id) as total_quizzes,
                        COUNT(DISTINCT CASE WHEN passed = 1 THEN quiz_id END) as passed_quizzes,
                        AVG(score_percentage) as avg_score,
                        MAX(score_percentage) as best_score,
                        COUNT(*) as total_attempts
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
