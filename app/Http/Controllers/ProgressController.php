<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\UserProgress;
use App\Models\LessonCompletion;
use App\Models\QuizAttempt;
use App\Models\LevelProgression;

class ProgressController extends Controller
{
    /**
     * Show user progress dashboard
     */
    public function index()
    {
        $userId = Auth::id();
        $currentLevel = session('selected_level_group', 'primary-lower');

        // Get or create progress record
        $progress = $this->getOrCreateProgress($userId, $currentLevel);

        // Get detailed statistics
        $lessonStats = LessonCompletion::getLevelStats($userId, $currentLevel);
        $quizStats = QuizAttempt::getLevelStats($userId, $currentLevel);
        $progressionHistory = LevelProgression::getUserHistory($userId);

        // Get recent activities
        $recentLessons = LessonCompletion::where('user_id', $userId)
            ->where('lesson_level', $currentLevel)
            ->orderBy('last_watched_at', 'desc')
            ->limit(5)
            ->get();

        $recentQuizzes = QuizAttempt::where('user_id', $userId)
            ->where('quiz_level', $currentLevel)
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get();

        // Check for level progression eligibility
        $progressionStatus = $this->checkProgressionEligibility($userId, $currentLevel);

        // Get detailed analytics
        $analytics = $progress->getDetailedAnalytics();

        return view('dashboard.my-progress', compact(
            'progress',
            'lessonStats',
            'quizStats',
            'progressionHistory',
            'recentLessons',
            'recentQuizzes',
            'progressionStatus',
            'currentLevel',
            'analytics'
        ));
    }

    /**
     * Record lesson watch progress
     */
    public function recordLessonProgress(Request $request, $lessonId)
    {
        $request->validate([
            'watch_time' => 'required|integer|min:0',
            'total_duration' => 'required|integer|min:1',
            'lesson_data' => 'required|array',
        ]);

        $userId = Auth::id();
        $lessonData = $request->lesson_data;

        // Add level_group to lesson data if not present
        if (!isset($lessonData['level_group'])) {
            $lessonData['level_group'] = $this->getLevelGroup($lessonData['level']);
        }

        // Record the lesson completion
        $completion = LessonCompletion::recordWatchProgress(
            $userId,
            $lessonData,
            $request->watch_time,
            $request->total_duration
        );

        // Update user progress
        $progress = $this->updateUserProgress($userId, $lessonData['level']);

        // Record activity and time spent
        if ($progress) {
            $progress->recordActivity();
            $progress->addTimeSpent($request->watch_time);
        }

        // Record detailed engagement for recommendation system
        $action = $completion->fully_completed ? 'complete' : 'view';
        \App\Models\UserEngagement::record(
            $userId,
            'lesson',
            $lessonData['id'],
            $action,
            $request->watch_time,
            [
                'title' => $lessonData['title'],
                'subject' => $lessonData['subject'],
                'level' => $lessonData['level'],
                'completion_percentage' => $completion->completion_percentage,
                'fully_completed' => $completion->fully_completed,
                'total_duration' => $request->total_duration,
            ]
        );

        Log::info('lesson_progress_recorded', [
            'user_id' => $userId,
            'lesson_id' => $lessonId,
            'watch_time' => $request->watch_time,
            'completion_percentage' => $completion->completion_percentage,
            'fully_completed' => $completion->fully_completed,
        ]);

        return response()->json([
            'success' => true,
            'completion_percentage' => $completion->completion_percentage,
            'fully_completed' => $completion->fully_completed,
            'message' => $completion->fully_completed ? 'Lesson completed!' : 'Progress saved',
        ]);
    }

    /**
     * Record quiz attempt
     */
    public function recordQuizAttempt(Request $request, $quizId)
    {
        $request->validate([
            'answers' => 'required|array',
            'time_taken' => 'required|integer|min:1',
            'quiz_data' => 'required|array',
        ]);

        $userId = Auth::id();
        $quizData = $request->quiz_data;

        // Add level_group to quiz data if not present
        if (!isset($quizData['level_group'])) {
            $quizData['level_group'] = $this->getLevelGroup($quizData['level']);
        }

        // Record the quiz attempt
        $attempt = QuizAttempt::recordAttempt(
            $userId,
            $quizData,
            $request->answers,
            $request->time_taken
        );

        // Update user progress
        $progress = $this->updateUserProgress($userId, $quizData['level']);

        // Record activity and time spent
        if ($progress) {
            $progress->recordActivity();
            $progress->addTimeSpent($request->time_taken);
        }

        // Record detailed engagement for recommendation system
        $action = $attempt->passed ? 'complete' : 'attempt';
        \App\Models\UserEngagement::record(
            $userId,
            'quiz',
            $quizData['id'],
            $action,
            $request->time_taken,
            [
                'title' => $quizData['title'],
                'subject' => $quizData['subject'],
                'level' => $quizData['level'],
                'score_percentage' => $attempt->score_percentage,
                'passed' => $attempt->passed,
                'attempt_number' => $attempt->attempt_number,
                'total_questions' => $attempt->total_questions,
                'correct_answers' => $attempt->correct_answers,
            ]
        );

        Log::info('quiz_attempt_recorded', [
            'user_id' => $userId,
            'quiz_id' => $quizId,
            'score_percentage' => $attempt->score_percentage,
            'passed' => $attempt->passed,
            'attempt_number' => $attempt->attempt_number,
        ]);

        return response()->json([
            'success' => true,
            'score_percentage' => $attempt->score_percentage,
            'passed' => $attempt->passed,
            'correct_answers' => $attempt->correct_answers,
            'total_questions' => $attempt->total_questions,
            'message' => $attempt->passed ? 'Quiz passed!' : 'Quiz completed. Try again to improve your score.',
        ]);
    }

    /**
     * Check and process level progression
     */
    public function checkProgression($level)
    {
        $userId = Auth::id();
        $progressionStatus = $this->checkProgressionEligibility($userId, $level);
        
        if ($progressionStatus['eligible']) {
            // Auto-progress the user
            $nextLevel = $this->getNextLevel($level);
            if ($nextLevel) {
                $this->progressUserToNextLevel($userId, $level, $nextLevel, $progressionStatus);
            }
        }

        return response()->json($progressionStatus);
    }

    /**
     * Manually progress user to next level (admin function)
     */
    public function manualProgression(Request $request, $userId, $toLevel)
    {
        $request->validate([
            'from_level' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        $fromLevel = $request->from_level;
        
        // Create manual progression record
        $progressData = [
            'from_level_group' => $this->getLevelGroup($fromLevel),
            'to_level_group' => $this->getLevelGroup($toLevel),
            'final_score' => 0,
            'lessons_completed' => 0,
            'quizzes_passed' => 0,
            'average_quiz_score' => 0,
            'criteria_met' => ['manual_progression' => true, 'reason' => $request->reason],
        ];

        LevelProgression::recordProgression($userId, $fromLevel, $toLevel, $progressData);

        // Update user's current level
        session(['selected_level_group' => $this->getLevelGroup($toLevel)]);

        Log::info('manual_level_progression', [
            'admin_user_id' => Auth::id(),
            'target_user_id' => $userId,
            'from_level' => $fromLevel,
            'to_level' => $toLevel,
            'reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User successfully progressed to next level',
        ]);
    }

    /**
     * Get or create progress record for user and level
     */
    private function getOrCreateProgress($userId, $level)
    {
        $progress = UserProgress::getCurrentProgress($userId, $level);
        
        if (!$progress) {
            // Get total lessons and quizzes for this level
            $totalLessons = $this->getTotalLessonsForLevel($level);
            $totalQuizzes = $this->getTotalQuizzesForLevel($level);
            
            $progress = UserProgress::create([
                'user_id' => $userId,
                'current_level' => $level,
                'level_group' => $this->getLevelGroup($level),
                'total_lessons_in_level' => $totalLessons,
                'total_quizzes_in_level' => $totalQuizzes,
                'level_started_at' => now(),
            ]);
        }
        
        return $progress;
    }

    /**
     * Update user progress based on completions
     */
    private function updateUserProgress($userId, $level)
    {
        $progress = $this->getOrCreateProgress($userId, $level);
        
        // Get current stats
        $lessonStats = LessonCompletion::getLevelStats($userId, $level);
        $quizStats = QuizAttempt::getLevelStats($userId, $level);
        
        // Update progress record
        $progress->update([
            'completed_lessons' => $lessonStats->completed_lessons ?? 0,
            'completed_quizzes' => $quizStats->passed_quizzes ?? 0,
            'average_quiz_score' => $quizStats->avg_score ?? 0,
        ]);
        
        // Update completion percentage and eligibility
        $progress->updateCompletionPercentage();
        $progress->calculateEligibility();
        
        return $progress;
    }

    /**
     * Check if user is eligible for level progression
     */
    private function checkProgressionEligibility($userId, $level)
    {
        $progress = $this->getOrCreateProgress($userId, $level);
        $isEligible = $progress->calculateEligibility();

        $nextLevel = $this->getNextLevel($level);
        $standards = \App\Models\ProgressionStandard::getStandardsForLevel($progress->level_group);

        return [
            'eligible' => $isEligible,
            'current_level' => $level,
            'next_level' => $nextLevel,
            'progress_data' => $progress->getPerformanceMetrics(),
            'requirements' => [
                'lesson_completion_required' => $standards['required_lesson_completion_percentage'],
                'quiz_completion_required' => $standards['required_quiz_completion_percentage'],
                'average_score_required' => $standards['required_average_quiz_score'],
            ],
            'message' => $isEligible
                ? "Congratulations! You're ready to progress to {$nextLevel}!"
                : 'Keep learning to unlock the next level!',
        ];
    }

    /**
     * Progress user to next level
     */
    private function progressUserToNextLevel($userId, $fromLevel, $toLevel, $progressionStatus)
    {
        $progress = UserProgress::getCurrentProgress($userId, $fromLevel);
        
        // Mark current level as completed
        $progress->update([
            'level_completed' => true,
            'level_completed_at' => now(),
        ]);
        
        // Record progression
        $progressData = [
            'from_level_group' => $this->getLevelGroup($fromLevel),
            'to_level_group' => $this->getLevelGroup($toLevel),
            'final_score' => $progress->completion_percentage,
            'lessons_completed' => $progress->completed_lessons,
            'quizzes_passed' => $progress->completed_quizzes,
            'average_quiz_score' => $progress->average_quiz_score,
            'criteria_met' => $progressionStatus['progress_data'],
        ];
        
        LevelProgression::recordProgression($userId, $fromLevel, $toLevel, $progressData);
        
        // Update session to new level
        session(['selected_level_group' => $this->getLevelGroup($toLevel)]);
        
        Log::info('automatic_level_progression', [
            'user_id' => $userId,
            'from_level' => $fromLevel,
            'to_level' => $toLevel,
            'final_score' => $progress->completion_percentage,
        ]);
    }

    /**
     * Helper methods
     */
    private function getTotalLessonsForLevel($level)
    {
        // This would typically come from your lessons database
        // For now, using the sample data structure
        $lessonCounts = [
            'primary-lower' => 12, // Combined P1-P3
            'primary-upper' => 8,  // Combined P4-P6
            'jhs' => 12,           // Combined JHS 1-3
            'shs' => 8,            // Combined SHS 1-3
        ];
        
        return $lessonCounts[$level] ?? 10;
    }
    
    private function getTotalQuizzesForLevel($level)
    {
        // This would typically come from your quizzes database
        $quizCounts = [
            'primary-lower' => 6,
            'primary-upper' => 4,
            'jhs' => 6,
            'shs' => 4,
        ];
        
        return $quizCounts[$level] ?? 5;
    }
    
    private function getLevelGroup($level)
    {
        $groups = [
            'primary-1' => 'primary-lower',
            'primary-2' => 'primary-lower',
            'primary-3' => 'primary-lower',
            'primary-4' => 'primary-upper',
            'primary-5' => 'primary-upper',
            'primary-6' => 'primary-upper',
            'jhs-1' => 'jhs',
            'jhs-2' => 'jhs',
            'jhs-3' => 'jhs',
            'shs-1' => 'shs',
            'shs-2' => 'shs',
            'shs-3' => 'shs',
        ];
        
        return $groups[$level] ?? $level;
    }
    
    private function getNextLevel($currentLevel)
    {
        $progression = [
            'primary-lower' => 'primary-upper',
            'primary-upper' => 'jhs',
            'jhs' => 'shs',
            'shs' => null, // No next level
        ];
        
        return $progression[$currentLevel] ?? null;
    }
}
