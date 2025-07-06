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
        
        return view('dashboard.quiz.results', compact('score', 'total', 'percentage', 'quiz'));
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
                'difficulty' => 'Easy',
                'created_at' => now()
            ],
            [
                'id' => 2,
                'title' => 'Basic Mathematics',
                'subject' => 'Math Gr1-3',
                'level_display' => 'Grade 1-3',
                'duration' => '5 min',
                'questions_count' => 15,
                'difficulty' => 'Easy',
                'created_at' => now()
            ],
            [
                'id' => 3,
                'title' => 'English Grammar Basics',
                'subject' => 'English Gr1-3',
                'level_display' => 'Grade 1-3',
                'duration' => '4 min',
                'questions_count' => 12,
                'difficulty' => 'Easy',
                'created_at' => now()
            ],
            // Add more sample quizzes as needed
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
