<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class QuizController extends Controller
{
    /**
     * Show quiz listing page
     */
    public function index(Request $request)
    {
        $selectedLevelGroup = session('selected_level_group', 'grade-1-3');
        $userId = Auth::id();

        // Cache key for base quiz data (changes less frequently)
        $cacheKey = "quizzes.{$selectedLevelGroup}";
        $cacheDuration = 300; // 5 minutes

        // Get base quiz data with caching
        $baseQuizzes = Cache::remember($cacheKey, $cacheDuration, function () use ($selectedLevelGroup) {
            $query = \App\Models\Quiz::with(['uploader', 'ratings', 'attempts']);

            // Filter by grade level if specified
            if ($selectedLevelGroup && $selectedLevelGroup !== 'all') {
                $query->where('grade_level', $selectedLevelGroup);
            }

            return $query->orderBy('created_at', 'desc')->get();
        });

        // Personalize with user-specific data (cached per user)
        $quizzes = $baseQuizzes->map(function ($quiz) use ($userId) {
            // Cache user-specific data for this quiz
            $userCacheKey = "quiz_user_data.{$userId}.{$quiz->id}";
            $userCacheDuration = 60; // 1 minute for user data

            return Cache::remember($userCacheKey, $userCacheDuration, function () use ($quiz, $userId) {
                // Get user's attempts for this quiz
                $userAttempts = $quiz->attempts()->where('user_id', $userId)
                    ->orderByDesc('completed_at')
                    ->get(['score_percentage', 'total_questions', 'correct_answers']);

                // Get user's rating for this quiz
                $userRating = $quiz->getUserRating($userId);

                // Parse quiz data for questions count
                $quizData = json_decode($quiz->quiz_data, true);
                $questionsCount = $quizData && isset($quizData['questions']) ? count($quizData['questions']) : 0;

                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'subject' => $quiz->subject,
                    'grade_level' => $quiz->grade_level,
                    'level_display' => $this->formatGradeLevel($quiz->grade_level),
                    'duration' => $quiz->formatted_time_limit,
                    'time_limit_minutes' => $quiz->time_limit_minutes,
                    'questions_count' => $questionsCount,
                    'difficulty' => $quiz->difficulty_level ?? 'medium',
                    'description' => $quiz->title . ' quiz covering ' . ($quiz->subject ?? 'various topics'),
                    'is_featured' => $quiz->is_featured,
                    'average_rating' => $quiz->average_rating,
                    'total_ratings' => $quiz->total_ratings,
                    'total_attempts_count' => $quiz->total_attempts_count,
                    'user_rating' => $userRating ? $userRating->rating : null,
                    'user_attempts_count' => $userAttempts->count(),
                    'user_progress' => $userAttempts->isNotEmpty() ? (int) round($userAttempts->first()->score_percentage) : 0,
                    'last_result' => $userAttempts->isNotEmpty() ? [
                        'score' => (int) ($userAttempts->first()->correct_answers ?? 0),
                        'total' => (int) ($userAttempts->first()->total_questions ?? 0),
                        'percentage' => (int) round($userAttempts->first()->score_percentage ?? 0),
                    ] : null,
                    'created_at' => $quiz->created_at,
                ];
            });
        });

        return view('dashboard.quiz.index', compact('quizzes', 'selectedLevelGroup'));
    }

    /**
     * Show quiz instructions page
     */
    public function instructions($quizId)
    {
        // Get quiz data - replace with actual database query
        $quiz = $this->getQuizById($quizId);
        
        if (!$quiz) {
            return redirect()->route('quiz.index')->with('error', 'Quiz not found.');
        }
        
        return view('dashboard.quiz.instructions', compact('quiz'));
    }
    
    //Convert duration to seconds
    private function convertDurationToSeconds($duration)
    {
        if (preg_match('/(\d+)\s*min/', $duration, $matches)) {
            return (int)$matches[1] * 60;
        }
        return 180; // Default to 3 minutes
    }

    /**
     * Show quiz taking page
     */
    public function take($quizId)
    {
        // Get quiz data and questions - replace with actual database queries
        $quiz = $this->getQuizById($quizId);
        
        if (!$quiz) {
            return redirect()->route('quiz.index')->with('error', 'Quiz not found.');
        }

        // Convert duration to seconds
        $seconds = $this->convertDurationToSeconds($quiz['duration']);
        
        // Check if user has already taken this quiz
         $hasAttempted = $this->checkUserAttempt($quizId, Auth::id());
        
        return view('dashboard.quiz.take', compact('quiz', 'seconds', 'hasAttempted'));
    }

    /**
     * Show essay quiz page
     */
    public function takeEssay($quizId)
    {
        $quiz = $this->getQuizById($quizId);
        if (!$quiz) {
            return redirect()->route('quiz.index')->with('error', 'Quiz not found.');
        }
        $seconds = $this->convertDurationToSeconds($quiz['duration']);
        $hasAttempted = $this->checkUserAttempt($quizId, Auth::id());
        return view('dashboard.quiz.essay', compact('quiz', 'seconds', 'hasAttempted'));
    }

    /**
     * Submit essay response
     */
    public function submitEssay(Request $request, $quizId)
    {
        $request->validate([
            'essay' => 'required|string|min:20|max:20000',
            'time_spent' => 'integer|min:0',
        ]);

        // TODO: Persist essay attempt; for now, redirect to results with placeholder scoring
        $timeSpent = (int) $request->input('time_spent', 0);
        // Placeholder: essay not auto-scored; show submitted status
        return redirect()->route('quiz.results', [
            'quiz' => $quizId,
            'score' => 0,
            'total' => 0,
            'percentage' => 0,
        ])->with('success', 'Essay submitted. It will be graded by an instructor.');
    }

    /**
     * Record a violation (anti-cheat)
     */
    public function violation(Request $request, $quizId)
    {
        $request->validate([
            'type' => 'required|string',
            'details' => 'nullable|string',
        ]);

        // Store violation record
        \App\Models\QuizViolation::recordViolation(
            Auth::id(),
            $quizId,
            $request->input('type'),
            $request->input('details')
        );

        return response()->json(['status' => 'ok']);
    }

    /**
     * Submit quiz answers
     */
    public function submit(Request $request, $quizId)
    {
        $request->validate([
            'answers' => 'array',
            'time_spent' => 'integer|min:0'
        ]);

        $answers = $request->input('answers', []);
        $timeSpent = $request->input('time_spent', 0);
        $failedDueToViolation = $request->input('failed_due_to_violation', false);
        
        // Process quiz submission
        $result = $this->processQuizSubmission($quizId, $answers, $timeSpent, $failedDueToViolation);
        
        return redirect()->route('quiz.results', [
            'quiz' => $quizId,
            'score' => $result['score'],
            'total' => $result['total'],
            'percentage' => $result['percentage'],
            'failed_due_to_violation' => $failedDueToViolation
        ]);
    }

    /**
     * Show quiz results
     */
    public function results(Request $request)
    {
        $score = $request->input('score', 0);
        $total = $request->input('total', 10);
        $percentage = $total > 0 ? round(($score / $total) * 100) : 0;
        $quizId = $request->input('quiz');
        $failedDueToViolation = $request->input('failed_due_to_violation', false);

        $quiz = $this->getQuizById($quizId);
        $duration = $quiz['duration'] ?? '3 min';

        return view('dashboard.quiz.results', compact('score', 'total', 'percentage', 'quiz', 'duration', 'failedDueToViolation'));
    }

    /**
     * Get sample quizzes (replace with database query)
     */
    private function getSampleQuizzes()
    {
        return [
            [
                'id' => 1,
                'title' => 'Living and Non Living organism',
                'subject' => 'Science Gr1-3',
                'level_display' => 'Grade 1-3',
                'duration' => '3 min',
                'questions_count' => 10,
                'difficulty' => 'easy',
                'description' => 'Learn about the differences between living and non-living things in our environment.',
                'attempts_count' => 0,
                'rating' => 0,
                'user_progress' => 0,
                'created_at' => now()
            ],
            [
                'id' => 2,
                'title' => 'Basic Mathematics',
                'subject' => 'Math Gr1-3',
                'level_display' => 'Grade 1-3',
                'duration' => '5 min',
                'questions_count' => 15,
                'difficulty' => 'easy',
                'description' => 'Practice fundamental math concepts including addition, subtraction, and number recognition.',
                'attempts_count' => 2,
                'rating' => 4,
                'user_progress' => 75,
                'created_at' => now()
            ],
            [
                'id' => 3,
                'title' => 'English Grammar Basics',
                'subject' => 'English Gr1-3',
                'level_display' => 'Grade 1-3',
                'duration' => '4 min',
                'questions_count' => 12,
                'difficulty' => 'easy',
                'description' => 'Master basic grammar rules, sentence structure, and vocabulary building.',
                'attempts_count' => 1,
                'rating' => 5,
                'user_progress' => 100,
                'created_at' => now()
            ],
            [
                'id' => 4,
                'title' => 'Colors and Shapes',
                'subject' => 'Art Gr1-3',
                'level_display' => 'Grade 1-3',
                'duration' => '3 min',
                'questions_count' => 8,
                'difficulty' => 'easy',
                'description' => 'Identify different colors, shapes, and their properties in everyday objects.',
                'attempts_count' => 0,
                'rating' => 0,
                'user_progress' => 0,
                'created_at' => now()
            ],
            [
                'id' => 5,
                'title' => 'Weather and Seasons',
                'subject' => 'Science Gr1-3',
                'level_display' => 'Grade 1-3',
                'duration' => '4 min',
                'questions_count' => 10,
                'difficulty' => 'medium',
                'description' => 'Explore different weather patterns and seasonal changes throughout the year.',
                'attempts_count' => 3,
                'rating' => 3,
                'user_progress' => 50,
                'created_at' => now()
            ],
            [
                'id' => 6,
                'title' => 'Simple Addition',
                'subject' => 'Math Gr1-3',
                'level_display' => 'Grade 1-3',
                'duration' => '6 min',
                'questions_count' => 20,
                'difficulty' => 'easy',
                'description' => 'Practice adding single-digit numbers with visual aids and step-by-step guidance.',
                'attempts_count' => 1,
                'rating' => 4,
                'user_progress' => 25,
                'created_at' => now()
            ],
        ];
    }

    /**
     * Get quiz by ID from database with caching
     */
    private function getQuizById($quizId)
    {
        $cacheKey = "quiz.{$quizId}";
        $cacheDuration = 600; // 10 minutes

        return Cache::remember($cacheKey, $cacheDuration, function () use ($quizId) {
            $quiz = \App\Models\Quiz::with('uploader')->find($quizId);

            if (!$quiz) {
                return null;
            }

            // Parse quiz_data JSON
            $quizData = json_decode($quiz->quiz_data, true);

            return [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'subject' => $quiz->subject,
                'grade_level' => $quiz->grade_level,
                'duration' => '3 min', // Default duration
                'questions_count' => $quizData && isset($quizData['questions']) ? count($quizData['questions']) : 0,
                'questions' => $quizData && isset($quizData['questions']) ? $quizData['questions'] : [],
                'uploader' => $quiz->uploader,
            ];
        });
    }

    /**
     * Process quiz submission
     */
    private function processQuizSubmission($quizId, $answers, $timeSpent, $failedDueToViolation = false)
    {
        $quiz = $this->getQuizById($quizId);
        if (!$quiz || !isset($quiz['questions'])) {
            return [
                'score' => 0,
                'total' => 0,
                'percentage' => 0
            ];
        }

        $questions = $quiz['questions'];
        $totalQuestions = count($questions);
        $correctAnswers = 0;

        foreach ($questions as $index => $question) {
            $userAnswer = isset($answers[$index]) ? (int)$answers[$index] : null;
            $correctAnswer = isset($question['correct']) ? (int)$question['correct'] : null;

            if ($userAnswer === $correctAnswer) {
                $correctAnswers++;
            }
        }

        $percentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100) : 0;

        // Save attempt to database
        $attempt = \App\Models\QuizAttempt::create([
            'user_id' => Auth::id(),
            'quiz_id' => $quizId,
            'score_percentage' => $percentage,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'time_spent_seconds' => $timeSpent,
            'failed_due_to_violation' => $failedDueToViolation,
            'completed_at' => now(),
        ]);

        // Clear user-specific cache for this quiz
        $userId = Auth::id();
        Cache::forget("quiz_user_data.{$userId}.{$quizId}");

        return [
            'score' => $correctAnswers,
            'total' => $totalQuestions,
            'percentage' => $percentage
        ];
    }

    /**
     * Check if the user has already attempted the quiz
     */
    private function checkUserAttempt($quizId, $userId)
    {
        return \App\Models\QuizAttempt::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Format grade level for display
     */
    private function formatGradeLevel($gradeLevel)
    {
        $levels = [
            'grade-1-3' => 'Grade 1-3',
            'grade-4-6' => 'Grade 4-6',
            'jhs' => 'Junior High School',
            'shs' => 'Senior High School',
            'tertiary' => 'Tertiary',
        ];

        return $levels[$gradeLevel] ?? ucfirst(str_replace('-', ' ', $gradeLevel));
    }

    /**
     * Calculate difficulty based on question count
     */
    private function calculateDifficulty($questionCount)
    {
        if ($questionCount <= 5) return 'easy';
        if ($questionCount <= 15) return 'medium';
        return 'hard';
    }

    /**
     * Rate a quiz
     */
    public function rate(Request $request, $quizId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:500',
        ]);

        $userId = Auth::id();

        // Find or create rating
        $rating = \App\Models\QuizRating::updateOrCreate(
            [
                'quiz_id' => $quizId,
                'user_id' => $userId,
            ],
            [
                'rating' => $request->rating,
                'review' => $request->review,
            ]
        );

        // Update quiz average rating
        $quiz = \App\Models\Quiz::find($quizId);
        if ($quiz) {
            $quiz->updateAverageRating();
        }

        // Clear user-specific cache for this quiz
        Cache::forget("quiz_user_data.{$userId}.{$quizId}");

        return response()->json([
            'success' => true,
            'message' => 'Rating submitted successfully',
            'rating' => $rating,
        ]);
    }

    /**
     * Clear quiz-related caches (helper method for cache invalidation)
     */
    private function clearQuizCaches($quizId = null, $userId = null)
    {
        if ($quizId) {
            // Clear specific quiz cache
            Cache::forget("quiz.{$quizId}");

            if ($userId) {
                // Clear user-specific data for this quiz
                Cache::forget("quiz_user_data.{$userId}.{$quizId}");
            } else {
                // Clear all user caches for this quiz (expensive, use sparingly)
                // This would require iterating through all users, so we skip it for now
            }
        }

        // Clear quiz listing caches for all grade levels
        $gradeLevels = ['grade-1-3', 'grade-4-6', 'jhs', 'shs', 'tertiary', 'all'];
        foreach ($gradeLevels as $level) {
            Cache::forget("quizzes.{$level}");
        }
    }
}
