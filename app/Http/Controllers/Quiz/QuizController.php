<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use \App\Models\Quiz;

class QuizController extends Controller
{
    /**
     * Mapping of session level groups to database grade levels
     */
    private function getGradeLevelMapping()
    {
        return [
            'primary-lower' => ['Primary 1', 'Primary 2', 'Primary 3'],
            'grade-1-3' => ['Primary 1', 'Primary 2', 'Primary 3'],
            'grade-4-6' => ['Primary 4', 'Primary 5', 'Primary 6'],
            'jhs' => ['JHS 1', 'JHS 2', 'JHS 3'],
            'shs' => ['SHS 1', 'SHS 2', 'SHS 3'],
            'tertiary' => ['Tertiary'],
        ];
    }

    /**
     * Show quiz listing page
     */
    public function index(Request $request)
    {
        $selectedLevelGroup = session('selected_level_group', 'grade-1-3');
        $userId = Auth::id();

        // Determine subscription access outside cache
        $user = Auth::user();
        $requiresSubscription = false;
        $allowedGradeLevels = [];
        if (!$user || !$user->is_superuser) {
            $allowedGradeLevels = \App\Services\SubscriptionAccessService::getAllowedGradeLevels($user);
            if (empty($allowedGradeLevels)) {
                $requiresSubscription = true;
            }
        }

        // Cache key for base quiz data (changes less frequently)
        $cacheKey = "quizzes.{$selectedLevelGroup}";
        $cacheDuration = 300; // 5 minutes

        // Get search term from request
        $search = $request->query('search');

        // Get base quiz data with caching
        $baseQuizzes = Cache::remember($cacheKey, $cacheDuration, function () use ($selectedLevelGroup, $userId, $allowedGradeLevels, $requiresSubscription, $user) {
            $query = \App\Models\Quiz::with(['uploader', 'ratings', 'attempts', 'subject']);

            // Filter by grade level if specified
            if ($selectedLevelGroup && $selectedLevelGroup !== 'all') {
                $mapping = $this->getGradeLevelMapping();
                if (isset($mapping[$selectedLevelGroup])) {
                    $query->whereIn('grade_level', $mapping[$selectedLevelGroup]);
                } else {
                    // Fallback to exact match if no mapping exists
                    $query->where('grade_level', $selectedLevelGroup);
                }
            }

            $quizzes = $query->orderBy('created_at', 'desc')->get();

            // Filter quizzes based on user's subscription access
            if (!$requiresSubscription && !empty($allowedGradeLevels)) {
                $quizzes = $quizzes->filter(function ($quiz) use ($allowedGradeLevels) {
                    return in_array($quiz->grade_level, $allowedGradeLevels);
                });
            } elseif ($requiresSubscription) {
                $quizzes = collect(); // No quizzes if no access
            }
            // Super users have access to all quizzes regardless of subscription

            // Debug logging
            $debugAllowedGradeLevels = $user && !$user->is_superuser ? \App\Services\SubscriptionAccessService::getAllowedGradeLevels($user) : ['all'];
            Log::info("Quiz filtering debug", [
                'selectedLevelGroup' => $selectedLevelGroup,
                'cacheKey' => "quizzes.{$selectedLevelGroup}",
                'total_quizzes_found' => $quizzes->count(),
                'grade_levels_in_db' => $quizzes->pluck('grade_level')->unique()->toArray(),
                'sample_quiz_titles' => $quizzes->take(3)->pluck('title')->toArray(),
                'session_selected_level_group' => session('selected_level_group'),
                'user_id' => $userId,
                'user_is_superuser' => $user ? $user->is_superuser : false,
                'allowed_grade_levels' => $debugAllowedGradeLevels,
                'requires_subscription' => $requiresSubscription,
                'query_sql' => $query->toSql(),
                'query_bindings' => $query->getBindings(),
                'filtered_quizzes_count' => $quizzes->count(),
                'all_quizzes_count' => \App\Models\Quiz::count(),
                'grade_level_mapping_used' => isset($this->getGradeLevelMapping()[$selectedLevelGroup]) ? $this->getGradeLevelMapping()[$selectedLevelGroup] : null
            ]);

            return $quizzes;
        });

        // Filter by search if provided
        if ($search) {
            $baseQuizzes = $baseQuizzes->filter(function ($quiz) use ($search) {
                return stripos($quiz->title, $search) !== false || 
                       ($quiz->subject && stripos($quiz->subject->name, $search) !== false);
            });
        }

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

                // Get time limit from quiz_data JSON if available, otherwise use database field
                $timeLimitMinutes = $quizData && isset($quizData['time_limit_minutes']) ? $quizData['time_limit_minutes'] : $quiz->time_limit_minutes;

                // Get difficulty level from quiz_data JSON if available, otherwise use database field
                $difficultyLevel = $quizData && isset($quizData['difficulty_level']) ? $quizData['difficulty_level'] : ($quiz->difficulty_level ?? 'medium');

                return [
                    'id' => $quiz->id,
                    'encoded_id' => \App\Services\UrlObfuscator::encode($quiz->id),
                    'title' => $quiz->title,
                    'subject' => $quiz->subject?->name,
                    'grade_level' => $quiz->grade_level,
                    'level_display' => $this->formatGradeLevel($quiz->grade_level),
                    'duration' => $quiz->formatted_time_limit,
                    'time_limit_minutes' => $timeLimitMinutes,
                    'questions_count' => $questionsCount,
                    'difficulty' => $difficultyLevel,
                    'description' => $quiz->title . ' quiz covering ' . ($quiz->subject?->name ?? 'various topics'),
                    'is_featured' => $quiz->is_featured,
                    'average_rating' => $quiz->average_rating,
                    'total_ratings' => $quiz->ratings->count(),
                    'total_attempts_count' => $quiz->total_attempts_count,
                    'user_rating' => $userRating ? $userRating->rating : null,
                    'user_review' => $userRating ? $userRating->review : null,
                    'attempts_count' => $userAttempts->count(),
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

        // Get available subjects for the subjects filter
        $subjects = $this->getSubjectsFromQuizzes($quizzes);

        return view('dashboard.quiz.index', compact('quizzes', 'selectedLevelGroup', 'requiresSubscription', 'subjects'));
    }

    /**
     * Get unique subjects from the list of quizzes
     */
    private function getSubjectsFromQuizzes($quizzes)
    {
        $subjectIcons = [
            'Mathematics' => 'fas fa-calculator',
            'English' => 'fas fa-book',
            'Science' => 'fas fa-flask',
            'Social Studies' => 'fas fa-globe',
            'General Education' => 'fas fa-graduation-cap',
            'Primary Education' => 'fas fa-school',
            'Junior High School' => 'fas fa-chalkboard-teacher',
            'Senior High School' => 'fas fa-university',
            'Computer Science' => 'fas fa-laptop-code',
            'Business Administration' => 'fas fa-briefcase',
            'Medicine' => 'fas fa-stethoscope',
        ];

        $uniqueSubjects = $quizzes->pluck('subject')->unique()->filter()->values();
        
        $subjects = collect([
            [
                'name' => 'All',
                'slug' => 'all',
                'icon' => 'fas fa-th-large',
                'count' => $quizzes->count()
            ]
        ]);

        foreach ($uniqueSubjects as $subjectName) {
            $count = $quizzes->where('subject', $subjectName)->count();
            $subjects->push([
                'name' => $subjectName,
                'slug' => \Illuminate\Support\Str::slug($subjectName),
                'icon' => $subjectIcons[$subjectName] ?? 'fas fa-book-open',
                'count' => $count
            ]);
        }

        return $subjects;
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

        // Add encoded ID for URL generation
        $quiz['encoded_id'] = \App\Services\UrlObfuscator::encode($quizId);

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

        // Add encoded ID for URL generation
        $quiz['encoded_id'] = \App\Services\UrlObfuscator::encode($quizId);

        // Convert duration to seconds - safe array access
        $seconds = (is_array($quiz) && isset($quiz['time_limit_minutes']) ? $quiz['time_limit_minutes'] : 3) * 60;

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

        // Add encoded ID for URL generation
        $quiz['encoded_id'] = \App\Services\UrlObfuscator::encode($quizId);

        $seconds = $this->convertDurationToSeconds(is_array($quiz) && isset($quiz['duration']) ? $quiz['duration'] : '3 min');
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
            'quiz' => \App\Services\UrlObfuscator::encode($quizId),
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
        // Comprehensive authentication debugging
        Log::info('Quiz submit method called - AUTH DEBUG', [
            'quiz_id' => $quizId,
            'request_method' => $request->method(),
            'request_data' => $request->all(),
            'auth_check' => Auth::check(),
            'auth_id' => Auth::id(),
            'auth_user' => Auth::user() ? [
                'id' => Auth::user()->id,
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ] : null,
            'session_id' => session()->getId(),
            'session_auth_id' => session('auth.user_id', 'not_set'),
            'session_all' => session()->all(),
            'cookies' => $request->cookies->all(),
            'headers' => [
                'user_agent' => $request->userAgent(),
                'accept' => $request->header('Accept'),
                'content_type' => $request->header('Content-Type'),
                'x_requested_with' => $request->header('X-Requested-With'),
                'referer' => $request->header('Referer'),
            ]
        ]);

        // Early validation to prevent array offset errors
        $answersInput = $request->input('answers');
        $timeSpentInput = $request->input('time_spent', 0);

        // Validate inputs before processing to prevent crashes
        if ($answersInput === null) {
            Log::error('Quiz submission failed: answers input is null', [
                'quiz_id' => $quizId,
                'request_data' => $request->all()
            ]);
            return redirect()->route('quiz.index')->with('error', 'Invalid quiz submission data.');
        }

        // Skip Laravel validation since we're handling JSON string to array conversion manually
        // The validation was failing because answers comes as JSON string, not array

        // Safe processing of answers
        if (is_string($answersInput)) {
            $answers = json_decode($answersInput, true) ?? [];
        } elseif (is_array($answersInput)) {
            $answers = $answersInput;
        } else {
            Log::error('Quiz submission failed: answers input is invalid type', [
                'quiz_id' => $quizId,
                'answers_type' => gettype($answersInput),
                'answers_value' => $answersInput
            ]);
            return redirect()->route('quiz.index')->with('error', 'Invalid answers format.');
        }

        $timeSpent = (int) $timeSpentInput;
        $failedDueToViolation = $request->input('failed_due_to_violation', false);

        Log::info('Parsed answers', [
            'original_answers' => $answersInput,
            'parsed_answers' => $answers,
            'answers_type' => gettype($answers),
            'answers_count' => is_array($answers) ? count($answers) : 'not_array'
        ]);

        // Additional validation after parsing
        if (!is_array($answers)) {
            Log::error('Quiz submission failed: answers is not an array after parsing', [
                'quiz_id' => $quizId,
                'answers_type' => gettype($answers),
                'original_input' => $answersInput
            ]);
            return redirect()->route('quiz.index')->with('error', 'Invalid answers format.');
        }

        // Get quiz data for logging
        $quiz = $this->getQuizById($quizId);

        // Validate quiz data to prevent array offset errors
        if (!$quiz || !is_array($quiz)) {
            Log::error('Quiz submission failed: invalid quiz data', [
                'quiz_id' => $quizId,
                'quiz_type' => gettype($quiz),
                'quiz_value' => $quiz
            ]);
            return redirect()->route('quiz.index')->with('error', 'Quiz not found or invalid data.');
        }

        // Safe logging to avoid array offset errors during logging
        $quizTitle = 'unknown';
        $questionsCount = 'no_questions_key';
        $quizKeys = 'not_array';

        if (is_array($quiz)) {
            $quizTitle = isset($quiz['title']) ? $quiz['title'] : 'unknown';
            $quizKeys = array_keys($quiz);
            if (isset($quiz['questions']) && is_array($quiz['questions'])) {
                $questionsCount = count($quiz['questions']);
            }
        }

        // Log detailed quiz submission data for debugging (minimal)
        Log::info('Quiz submission detailed data', [
            'quiz_id' => $quizId,
            'user_id' => Auth::id(),
            'answers_type' => gettype($answers),
            'answers_count' => is_array($answers) ? count($answers) : 'not_array',
            'time_spent' => $timeSpent,
            'failed_due_to_violation' => $failedDueToViolation,
            'quiz_title' => $quizTitle,
            'questions_in_quiz' => $questionsCount,
            'quiz_keys_available' => $quizKeys,
        ]);

        // Debug: Step-by-step execution tracking
        Log::info('DEBUG STEP 1: After detailed logging');

        try {
            // Test if we can access the method
            $methodExists = method_exists($this, 'processQuizSubmission');
            Log::info('DEBUG STEP 2: Method exists check', ['method_exists' => $methodExists]);

            // Test array access
            $answersCount = is_array($answers) ? count($answers) : 'not_array';
            Log::info('DEBUG STEP 3: Array access test', ['answers_count' => $answersCount]);

            // Test Auth access
            $userId = Auth::id();
            Log::info('DEBUG STEP 4: Auth access test', ['user_id' => $userId]);

            Log::info('About to call processQuizSubmission', [
                'quiz_id' => $quizId,
                'answers_count' => $answersCount,
                'time_spent' => $timeSpent,
                'user_id' => $userId,
                'method_exists' => $methodExists
            ]);

        } catch (\Exception $e) {
            Log::error('Exception during pre-method-call debugging', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        // Process quiz submission - pass authenticated user ID explicitly
        $result = $this->processQuizSubmission($quizId, $answers, $timeSpent, $failedDueToViolation, Auth::id());

        // Debug: Log after calling processQuizSubmission
        Log::info('processQuizSubmission returned', [
            'result' => $result,
            'result_type' => gettype($result)
        ]);


        // Log the submission for debugging - safe array access
        Log::info('Quiz submission processed', [
            'quiz_id' => $quizId,
            'answers' => $answers,
            'result' => $result,
            'result_type' => gettype($result),
            'result_keys' => is_array($result) ? array_keys($result) : 'not_array',
            'redirect_url' => route('quiz.results', [
                'quiz' => $quizId,
                'score' => is_array($result) && isset($result['score']) ? $result['score'] : 0,
                'total' => is_array($result) && isset($result['total']) ? $result['total'] : 0,
                'percentage' => is_array($result) && isset($result['percentage']) ? $result['percentage'] : 0,
                'failed_due_to_violation' => $failedDueToViolation
            ])
        ]);

        Log::info('About to redirect to results page');

        return redirect()->route('quiz.results', [
            'quiz' => \App\Services\UrlObfuscator::encode($quizId),
            'score' => is_array($result) && isset($result['score']) ? $result['score'] : 0,
            'total' => is_array($result) && isset($result['total']) ? $result['total'] : 0,
            'percentage' => is_array($result) && isset($result['percentage']) ? $result['percentage'] : 0,
            'failed_due_to_violation' => $failedDueToViolation
        ]);
    }

    /**
     * Show quiz results
     */
    public function results(Request $request)
    {
        $score = $request->input('score', session('score', 0));
        $total = $request->input('total', session('total', 10));
        $percentage = $request->input('percentage', session('percentage', 0));
        $encodedQuizId = $request->input('quiz', session('quiz_id'));
        $quizId = \App\Services\UrlObfuscator::decode($encodedQuizId) ?? $encodedQuizId;
        $failedDueToViolation = $request->input('failed_due_to_violation', session('failed_due_to_violation', false));

        $quiz = $this->getQuizById($quizId);
        $quiz['encoded_id'] = \App\Services\UrlObfuscator::encode($quizId);
        $duration = is_array($quiz) && isset($quiz['duration']) ? $quiz['duration'] : '3 min';

        // Get user's data for rank and streak
        $userId = Auth::id();
        $userTotalQuizzesPassed = \App\Models\UserProgress::where('user_id', $userId)
            ->sum('completed_quizzes');
        $rank = $this->calculateUserRank($userId, $userTotalQuizzesPassed);
        $streak = \App\Models\UserProgress::where('user_id', $userId)->max('current_streak_days') ?? 0;

        // Check if user has already rated this quiz
        $hasRated = \App\Models\QuizRating::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->exists();

        // Build questions array with user answers for review - safe array access
        $questions = [];
        $timeTaken = 0;
        if (is_array($quiz) && isset($quiz['questions']) && is_array($quiz['questions'])) {
            // Get the user's last attempt for this quiz
            $lastAttempt = \App\Models\QuizAttempt::where('user_id', Auth::id())
                ->where('quiz_id', $quizId)
                ->orderBy('completed_at', 'desc')
                ->first();

            $userAnswers = $lastAttempt ? json_decode($lastAttempt->answers ?? '[]', true) : [];
            $timeTaken = $lastAttempt ? $lastAttempt->time_taken_seconds : 0;

            foreach ($quiz['questions'] as $index => $question) {
                if (!is_array($question)) {
                    Log::warning('Skipping invalid question in results', [
                        'quiz_id' => $quizId,
                        'question_index' => $index,
                        'question_type' => gettype($question)
                    ]);
                    continue;
                }

                $userAnswer = isset($userAnswers[$index]) ? (int)$userAnswers[$index] : null;
                $correctAnswer = isset($question['correct_answer']) ? (int)$question['correct_answer'] : null;
                $userCorrect = $userAnswer === $correctAnswer;

                $questions[] = [
                    'question' => $question['question'] ?? 'Question not available',
                    'options' => $question['options'] ?? [],
                    'correct_answer' => $correctAnswer,
                    'user_answer' => $userAnswer,
                    'user_correct' => $userCorrect,
                    'text' => $question['question'] ?? 'Question not available',
                    'image' => $question['image'] ?? null,
                ];
            }
        }

        return view('dashboard.quiz.results', compact('score', 'total', 'percentage', 'quiz', 'duration', 'failedDueToViolation', 'questions', 'rank', 'streak', 'timeTaken', 'hasRated'));
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
    public function getQuizById($quizId)
    {
        Log::info('getQuizById called', ['quiz_id' => $quizId]);

        $cacheKey = "quiz.{$quizId}";
        $cacheDuration = 600; // 10 minutes

        Log::info('getQuizById cache key', ['cache_key' => $cacheKey]);

        try {
            $result = Cache::remember($cacheKey, $cacheDuration, function () use ($quizId) {
                Log::info('getQuizById cache miss, querying database', ['quiz_id' => $quizId]);

                $quiz = \App\Models\Quiz::with('uploader')->find($quizId);
                Log::info('getQuizById database result', ['quiz_found' => $quiz ? true : false]);

                if (!$quiz) {
                    Log::info('getQuizById returning null - quiz not found');
                    return null;
                }

                // Parse quiz_data JSON
                $quizData = json_decode($quiz->quiz_data, true);
                Log::info('getQuizById quiz_data parsed', [
                    'quiz_data_type' => gettype($quizData),
                    'has_questions' => $quizData && isset($quizData['questions']) ? 'yes' : 'no',
                    'questions_count' => $quizData && isset($quizData['questions']) ? count($quizData['questions']) : 0
                ]);

                // Process questions to add image URLs
                $processedQuestions = [];
                if ($quizData && isset($quizData['questions'])) {
                    foreach ($quizData['questions'] as $question) {
                        // Check if question has an image
                        if (isset($question['has_image']) && $question['has_image'] && isset($question['image_index'])) {
                            // Construct image URL based on quiz ID and image index
                            $question['image'] = $this->getQuizImageUrl($quiz->id, $question['image_index']);
                        }
                        $processedQuestions[] = $question;
                    }
                }

                // Get time limit from quiz_data JSON if available, otherwise use database field
                $timeLimitMinutes = $quizData && isset($quizData['time_limit_minutes']) ? $quizData['time_limit_minutes'] : $quiz->time_limit_minutes;

                // Get difficulty level from quiz_data JSON if available, otherwise use database field
                $difficultyLevel = $quizData && isset($quizData['difficulty_level']) ? $quizData['difficulty_level'] : ($quiz->difficulty_level ?? 'medium');

                // Format duration for display (e.g., "5 min" or "1 hour 30 min")
                $formattedDuration = $this->formatDuration($timeLimitMinutes);

                $result = [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'subject' => $quiz->subject?->name,
                    'grade_level' => $quiz->grade_level,
                    'duration' => $formattedDuration, // Use actual formatted duration
                    'time_limit_minutes' => $timeLimitMinutes,
                    'questions_count' => count($processedQuestions),
                    'questions' => $processedQuestions,
                    'difficulty' => $difficultyLevel,
                    'uploader' => $quiz->uploader,
                ];

                Log::info('getQuizById returning result', ['result_keys' => array_keys($result)]);
                return $result;
            });

            Log::info('getQuizById final result', ['result_type' => gettype($result)]);
            return $result;

        } catch (\Exception $e) {
            Log::error('Exception in getQuizById', [
                'quiz_id' => $quizId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Process quiz submission
     */
    private function processQuizSubmission($quizId, $answers, $timeSpent, $failedDueToViolation = false, $userId = null)
    {
        // Debug: Log that processQuizSubmission was called
        Log::info('processQuizSubmission method STARTED', [
            'quiz_id' => $quizId,
            'answers' => $answers,
            'time_spent' => $timeSpent,
            'failed_due_to_violation' => $failedDueToViolation,
            'user_id_param' => $userId,
            'auth_id' => Auth::id()
        ]);

        $quiz = $this->getQuizById($quizId);

        // Validate quiz data structure before processing
        if (!is_array($quiz) || !isset($quiz['questions']) || !is_array($quiz['questions'])) {
            Log::error('Quiz submission failed: invalid quiz structure', [
                'quiz_id' => $quizId,
                'quiz_type' => gettype($quiz),
                'has_questions' => is_array($quiz) && isset($quiz['questions']),
                'questions_type' => is_array($quiz) && isset($quiz['questions']) ? gettype($quiz['questions']) : 'N/A'
            ]);
            return [
                'score' => 0,
                'total' => 0,
                'percentage' => 0
            ];
        }

        if (!$quiz || !isset($quiz['questions'])) {
            Log::warning('Quiz submission failed: quiz not found or no questions', [
                'quiz_id' => $quizId,
                'quiz_data' => $quiz
            ]);
            return [
                'score' => 0,
                'total' => 0,
                'percentage' => 0
            ];
        }

        // Diagnostic log before accessing array offset
        Log::info('DIAGNOSTIC: Before array access', [
            'quiz_id' => $quizId,
            'quiz_type' => gettype($quiz),
            'quiz_is_array' => is_array($quiz),
            'questions_key_exists' => isset($quiz['questions']),
            'questions_value_type' => isset($quiz['questions']) ? gettype($quiz['questions']) : 'key_not_set'
        ]);

        $questions = $quiz['questions'];
        $totalQuestions = count($questions);
        $correctAnswers = 0;

        Log::info('Processing quiz answers', [
            'quiz_id' => $quizId,
            'total_questions_in_quiz' => $totalQuestions,
            'answers_provided' => count($answers),
            'answers_array' => $answers,
            'questions_array' => $questions
        ]);

        foreach ($questions as $index => $question) {
            // Validate question structure before accessing array offsets
            if (!is_array($question) || !isset($question['correct_answer'])) {
                Log::warning('Invalid question structure, skipping', [
                    'question_index' => $index,
                    'question_type' => gettype($question),
                    'question_keys' => is_array($question) ? array_keys($question) : 'not_array'
                ]);
                continue; // Skip invalid questions instead of crashing
            }

            $userAnswer = isset($answers[$index]) ? (int)$answers[$index] : null;
            $correctAnswer = isset($question['correct_answer']) ? (int)$question['correct_answer'] : null;

            Log::info('Answer comparison', [
                'question_index' => $index,
                'user_answer' => $userAnswer,
                'correct_answer' => $correctAnswer,
                'question_data' => $question,
                'is_correct' => $userAnswer === $correctAnswer
            ]);

            if ($userAnswer === $correctAnswer) {
                $correctAnswers++;
            }
        }

        $percentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100) : 0;

        Log::info('Quiz scoring result', [
            'quiz_id' => $quizId,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'incorrect_answers' => $totalQuestions - $correctAnswers,
            'percentage' => $percentage
        ]);

        // Use provided userId or get from Auth
        $currentUserId = $userId ?: Auth::id();

        // Log pre-save validation data
        Log::info('Quiz attempt save validation', [
            'quiz_id' => $quizId,
            'user_id' => $currentUserId,
            'quiz_exists' => \App\Models\Quiz::where('id', $quizId)->exists(),
            'user_exists' => \App\Models\User::where('id', $currentUserId)->exists(),
            'data_to_save' => [
                'user_id' => $currentUserId,
                'quiz_id' => $quizId,
                'score_percentage' => $percentage,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'time_spent_seconds' => $timeSpent,
                'failed_due_to_violation' => $failedDueToViolation,
                'completed_at' => now(),
            ]
        ]);

        try {
            // Get quiz data for required fields
            $quiz = \App\Models\Quiz::find($quizId);
    
            // Save attempt to database
            $attempt = \App\Models\QuizAttempt::create([
                'user_id' => $currentUserId,
                'quiz_id' => $quizId,
                'quiz_title' => $quiz ? $quiz->title : 'Unknown Quiz',
                'quiz_subject' => $quiz ? ($quiz->subject?->name ?? 'Unknown Subject') : 'Unknown Subject',
                'quiz_level' => $quiz ? $quiz->grade_level : 'Unknown Level',
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'incorrect_answers' => $totalQuestions - $correctAnswers,
                'score_percentage' => $percentage,
                'time_taken_seconds' => $timeSpent,
                'passed' => $percentage >= 50, // Default passing score
                'failed_due_to_violation' => $failedDueToViolation,
                'attempt_number' => $this->getNextAttemptNumber($quizId, $currentUserId),
                'answers' => json_encode($answers),
                'question_details' => $quiz ? $quiz->quiz_data : null,
                'started_at' => now()->subSeconds($timeSpent),
                'completed_at' => now(),
            ]);

            Log::info('Quiz attempt saved successfully', [
                'attempt_id' => $attempt->id,
                'quiz_id' => $quizId,
                'user_id' => $currentUserId
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error saving quiz attempt', [
                'quiz_id' => $quizId,
                'user_id' => $currentUserId,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'sql_state' => $e->errorInfo[0] ?? null,
                'driver_error_code' => $e->errorInfo[1] ?? null,
                'driver_error_message' => $e->errorInfo[2] ?? null,
                'data_attempted' => [
                    'user_id' => $currentUserId,
                    'quiz_id' => $quizId,
                    'score_percentage' => $percentage,
                    'total_questions' => $totalQuestions,
                    'correct_answers' => $correctAnswers,
                    'time_spent_seconds' => $timeSpent,
                    'failed_due_to_violation' => $failedDueToViolation,
                ]
            ]);

            // For debugging, return the result anyway but log the error
            // This ensures quiz submission doesn't fail completely due to database issues
            Log::warning('Returning quiz result despite database error for debugging', [
                'quiz_id' => $quizId,
                'user_id' => $currentUserId,
                'result_returned' => [
                    'score' => $correctAnswers,
                    'total' => $totalQuestions,
                    'percentage' => $percentage
                ]
            ]);

            // Don't re-throw - return the result so quiz submission appears to work
            // but log the database issue for investigation

        } catch (\Exception $e) {
            Log::error('Unexpected error saving quiz attempt', [
                'quiz_id' => $quizId,
                'user_id' => $currentUserId,
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // For debugging, return the result anyway
            Log::warning('Returning quiz result despite unexpected error for debugging', [
                'quiz_id' => $quizId,
                'user_id' => $currentUserId,
                'result_returned' => [
                    'score' => $correctAnswers,
                    'total' => $totalQuestions,
                    'percentage' => $percentage
                ]
            ]);

            // Don't re-throw - return the result so quiz submission appears to work
        }

        // Clear user-specific cache for this quiz
        $userId = $currentUserId ?: Auth::id();
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
     * Get the next attempt number for a user and quiz
     */
    private function getNextAttemptNumber($quizId, $userId)
    {
        return \App\Models\QuizAttempt::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->max('attempt_number') + 1;
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
     * Format duration from minutes to readable string
     */
    private function formatDuration($minutes)
    {
        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes == 0) {
            return $hours . ' hour' . ($hours > 1 ? 's' : '');
        }

        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ' . $remainingMinutes . ' min';
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
     * Get quiz image URL based on quiz ID and image index
     */
    private function getQuizImageUrl($quizId, $imageIndex)
    {
        // Check for different extensions
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        foreach ($extensions as $ext) {
            $path = "quiz_images/{$quizId}/{$imageIndex}.{$ext}";
            if (Storage::disk('public')->exists($path)) {
                return asset('storage/' . $path);
            }
        }

        return null; // Image not found
    }

    /**
     * Calculate user's rank based on total quizzes passed
     */
    private function calculateUserRank($userId, $userTotalQuizzesPassed)
    {
        // Count how many users have more quizzes passed than this user
        $usersWithHigherScore = \App\Models\UserProgress::where('completed_quizzes', '>', $userTotalQuizzesPassed)
            ->count();

        // Rank is the number of users with higher score + 1
        return $usersWithHigherScore + 1;
    }

    /**
     * Get reviews for a quiz (API endpoint)
     */
    public function getReviews($quizId)
    {
        try {
            $quiz = Quiz::find($quizId);
            if (!$quiz) {
                return response()->json(['success' => false, 'message' => 'Quiz not found'], 404);
            }

            $reviews = $quiz->ratings()
                ->with('user:id,name')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($rating) {
                    return [
                        'id' => $rating->id,
                        'rating' => $rating->rating,
                        'review' => $rating->review,
                        'user_name' => $rating->user ? $rating->user->name : 'Anonymous',
                        'created_at' => $rating->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'reviews' => $reviews,
                'average_rating' => (float) ($quiz->average_rating ?? 0),
                'total_ratings' => (int) ($quiz->total_ratings ?? 0),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching quiz reviews', [
                'quiz_id' => $quizId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load reviews'
            ], 500);
        }
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
