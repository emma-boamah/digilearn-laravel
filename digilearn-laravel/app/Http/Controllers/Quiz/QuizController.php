<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    /**
     * Show quiz listing page
     */
    public function index(Request $request)
    {
        $selectedLevelGroup = session('selected_level_group', 'grade-1-3');
        
        // Sample quiz data - replace with actual database queries
        $quizzes = $this->getSampleQuizzes();

        // Personalize with user-specific progress/attempts
        $userId = Auth::id();
        foreach ($quizzes as &$quiz) {
            $attempts = \App\Models\QuizAttempt::where('user_id', $userId)
                ->where('quiz_id', $quiz['id'])
                ->orderByDesc('completed_at')
                ->get(['score_percentage', 'total_questions', 'correct_answers']);

            $quiz['attempts_count'] = $attempts->count();
            if ($attempts->isNotEmpty()) {
                $last = $attempts->first();
                $quiz['user_progress'] = (int) round($last->score_percentage);
                $quiz['last_result'] = [
                    'score' => (int) ($last->correct_answers ?? 0),
                    'total' => (int) ($last->total_questions ?? 0),
                    'percentage' => (int) round($last->score_percentage ?? 0),
                ];
            } else {
                $quiz['user_progress'] = 0;
                $quiz['last_result'] = null;
            }
        }
        
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

        // TODO: Store violation record; for now, mark as failed and redirect
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
        
        // Process quiz submission
        $result = $this->processQuizSubmission($quizId, $answers, $timeSpent);
        
        return redirect()->route('quiz.results', [
            'quiz' => $quizId,
            'score' => $result['score'],
            'total' => $result['total'],
            'percentage' => $result['percentage']
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
        
        $quiz = $this->getQuizById($quizId);
        $duration = $quiz['duration'] ?? '3 min';
        
        return view('dashboard.quiz.results', compact('score', 'total', 'percentage', 'quiz', 'duration'));
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
     * Get quiz by ID (replace with database query)
     */
    private function getQuizById($quizId)
    {
        $quizzes = $this->getSampleQuizzes();
        
        foreach ($quizzes as $quiz) {
            if ($quiz['id'] == $quizId) {
                return $quiz;
            }
        }
        
        return null;
    }

    /**
     * Process quiz submission
     */
    private function processQuizSubmission($quizId, $answers, $timeSpent)
    {
        // This is where you would:
        // 1. Get correct answers from database
        // 2. Compare with user answers
        // 3. Calculate score
        // 4. Save attempt to database
        
        $totalQuestions = 10; // Get from database
        $correctAnswers = count($answers); // Simplified - replace with actual scoring logic
        $percentage = round(($correctAnswers / $totalQuestions) * 100);
        
        return [
            'score' => $correctAnswers,
            'total' => $totalQuestions,
            'percentage' => $percentage
        ];
    }

    /**
     * Check if the user has already attempted the quiz
     * (Stub implementation, replace with actual database check)
     */
    private function checkUserAttempt($quizId, $userId)
    {
        // TODO: Replace with actual logic to check if user has attempted the quiz
        return false;
    }
}
