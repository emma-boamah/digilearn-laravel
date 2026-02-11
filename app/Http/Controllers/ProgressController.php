<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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

        // Get detailed statistics from database
        $lessonStats = LessonCompletion::getLevelGroupStats($userId, $currentLevel);
        $quizStats = QuizAttempt::getLevelGroupStats($userId, $currentLevel);
        $progressionHistory = LevelProgression::getUserHistory($userId);

        // SYNC: Ensure UserProgress record is up-to-date with calculated stats
        // This fixes the 'Overall Progress 0%' issue by propagating the 'Quiz Count' fix to the main record
        $progress->update([
            'completed_lessons' => $lessonStats->completed_lessons ?? 0,
            'completed_quizzes' => $quizStats->passed_quizzes ?? 0,
            'average_quiz_score' => $quizStats->avg_score ?? 0,
            'total_lessons_in_level' => $this->getTotalLessonsForLevel($currentLevel),
            'total_quizzes_in_level' => $this->getTotalQuizzesForLevel($currentLevel),
        ]);
        $progress->updateCompletionPercentage(); // Recalculate overall % based on new values
        $progress->save();

        // Debug logging to verify data is coming from database
        Log::info('Progress page data sources', [
            'user_id' => $userId,
            'current_level' => $currentLevel,
            'progress_from_db' => [
                'id' => $progress->id,
                'user_id' => $progress->user_id,
                'current_level' => $progress->current_level,
                'completed_lessons' => $progress->completed_lessons,
                'completed_quizzes' => $progress->completed_quizzes,
                'completion_percentage' => $progress->completion_percentage,
                'average_quiz_score' => $progress->average_quiz_score,
                'total_time_spent_seconds' => $progress->total_time_spent_seconds,
            ],
            'lesson_stats_type' => gettype($lessonStats),
            'quiz_stats_type' => gettype($quizStats),
            'progression_history_count' => $progressionHistory ? $progressionHistory->count() : 0,
        ]);

        // Get recent activities
        $recentLessons = LessonCompletion::where('user_id', $userId)
            ->where('lesson_level_group', $currentLevel)
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

        // Get progression standards for the current level group
        $standards = \App\Models\ProgressionStandard::getStandardsForLevel($currentLevel);

        return view('dashboard.my-progress', compact(
            'progress',
            'lessonStats',
            'quizStats',
            'progressionHistory',
            'recentLessons',
            'recentQuizzes',
            'progressionStatus',
            'currentLevel',
            'analytics',
            'standards'
        ));
    }

    /**
     * Record lesson watch progress
     */
    public function recordLessonProgress(Request $request, $lessonId)
    {
        $request->validate([
            'watch_time' => 'required|integer|min:0',
            'current_timestamp' => 'nullable|integer|min:0',
            'total_duration' => 'nullable|numeric|min:1',
            'lesson_data' => 'required|array',
        ]);

        $userId = Auth::id();
        $lessonData = $request->lesson_data;

        // Add level_group to lesson data if not present
        if (!isset($lessonData['level_group'])) {
            $lessonData['level_group'] = $this->getLevelGroup($lessonData['level']);
        }

        // CRITICAL: Get accurate total duration from video source
        $totalDuration = null;

        if (isset($lessonData['id'])) {
            $video = \App\Models\Video::find($lessonData['id']);
            if ($video) {
                // Try to get actual duration from the video source
                $videoDuration = $video->getDuration();

                if ($videoDuration && $videoDuration > 0) {
                    $totalDuration = $videoDuration;
                    Log::info('Using video source duration', [
                        'video_id' => $lessonData['id'],
                        'duration' => $totalDuration,
                        'source' => $video->video_source
                    ]);
                } else {
                    // Fallback to requested duration or default
                    $totalDuration = $request->total_duration ?: 300;
                    Log::warning('Could not get video duration, using fallback', [
                        'video_id' => $lessonData['id'],
                        'fallback_duration' => $totalDuration,
                        'video_source' => $video->video_source
                    ]);
                }
            } else {
                // Video not found
                $totalDuration = $request->total_duration ?: 300;
                Log::warning('Video not found for lesson', [
                    'lesson_id' => $lessonId,
                    'video_id' => $lessonData['id']
                ]);
            }
        } else {
            // No video ID provided
            $totalDuration = $request->total_duration ?: 300;
        }

        Log::info('recordLessonProgress - Duration Summary', [
            'lesson_id' => $lessonId,
            'video_id' => $lessonData['id'] ?? null,
            'requested_duration' => $request->total_duration,
            'final_duration' => $totalDuration,
            'watch_time' => $request->watch_time
        ]);

        // Merge current_timestamp into lessonData for the model
        if ($request->has('current_timestamp')) {
            $lessonData['current_timestamp'] = $request->current_timestamp;
        }

        // Record the lesson completion
        $completion = LessonCompletion::recordWatchProgress(
            $userId,
            $lessonData,
            $request->watch_time,
            $totalDuration
        );

        // Update user progress using level group
        $levelGroup = $this->getLevelGroup($lessonData['level']);
        $progress = $this->updateUserProgress($userId, $levelGroup);

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

        // Update user progress using level group
        $levelGroup = $this->getLevelGroup($quizData['level']);
        $progress = $this->updateUserProgress($userId, $levelGroup);

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
            // First check if user should progress within their current level group
            $progress = UserProgress::getCurrentProgress($userId, $level);

            if ($progress && $progress->shouldProgressWithinLevelGroup()) {
                // Progress within level group (e.g., Primary 1 → Primary 2)
                $progressedWithinGroup = $progress->progressWithinLevelGroup();

                if ($progressedWithinGroup) {
                    // Update progression status to reflect within-group progression
                    $progressionStatus['progressed_within_group'] = true;
                    $progressionStatus['new_individual_level'] = $progress->user->grade;
                    $progressionStatus['message'] = "Congratulations! You've advanced to " . $progress->user->grade . "!";

                    Log::info('User progressed within level group', [
                        'user_id' => $userId,
                        'from_level' => $level,
                        'to_level' => $progress->user->grade,
                        'level_group' => $progress->level_group,
                    ]);
                }
            } else {
                // Progress to next level group (e.g., primary-lower → primary-upper)
                $nextLevel = $this->getNextLevel($level);
                if ($nextLevel) {
                    // Update lesson_completions table when user meets progression thresholds
                    $this->updateLessonCompletionsForProgression($userId, $level);

                    $this->progressUserToNextLevel($userId, $level, $nextLevel, $progressionStatus);
                    $progressionStatus['progressed_to_group'] = true;
                    $progressionStatus['new_level_group'] = $nextLevel;
                }
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
    private function updateUserProgress($userId, $levelGroup)
    {
        // $levelGroup is already converted to level group
        $progress = $this->getOrCreateProgress($userId, $levelGroup);

        // Get current stats for the level group
        $lessonStats = LessonCompletion::getLevelGroupStats($userId, $levelGroup);
        $quizStats = QuizAttempt::getLevelGroupStats($userId, $levelGroup);

        // Debug logging
        Log::info('updateUserProgress called', [
            'user_id' => $userId,
            'level_group' => $levelGroup,
            'lesson_stats' => $lessonStats ? $lessonStats->toArray() : null,
            'quiz_stats' => $quizStats ? $quizStats->toArray() : null,
        ]);

        // Update progress record
        $progress->update([
            'completed_lessons' => $lessonStats->completed_lessons ?? 0,
            'completed_quizzes' => $quizStats->passed_quizzes ?? 0,
            'average_quiz_score' => $quizStats->avg_score ?? 0,
        ]);

        // Update completion percentage and eligibility
        $progress->updateCompletionPercentage();
        $progress->calculateEligibility();

        Log::info('updateUserProgress completed', [
            'user_id' => $userId,
            'level_group' => $levelGroup,
            'updated_completed_lessons' => $progress->completed_lessons,
            'updated_completed_quizzes' => $progress->completed_quizzes,
            'updated_average_score' => $progress->average_quiz_score,
            'updated_completion_percentage' => $progress->completion_percentage,
        ]);

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
            'individual_progression_available' => $progress->shouldProgressWithinLevelGroup() !== null,
            'next_individual_level' => $progress->shouldProgressWithinLevelGroup(),
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

        // Update user's grade level in the users table
        $this->updateUserGradeLevel($userId, $toLevel);

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
     * Update lesson_completions table when user meets progression thresholds
     */
    private function updateLessonCompletionsForProgression($userId, $levelGroup)
    {
        try {
            // Get all quiz attempts for this user and level group
            $quizAttempts = \App\Models\QuizAttempt::where('user_id', $userId)
                ->where('quiz_level', $levelGroup)
                ->get();

            // Create lesson completion records for quizzes that were passed
            // This ensures they appear in the user's history even if they progressed via other means
            foreach ($quizAttempts as $attempt) {
                if ($attempt->passed) {
                    // Check if we already have a completion record for this quiz
                    $existingCompletion = \App\Models\LessonCompletion::where('user_id', $userId)
                        ->where('lesson_id', $attempt->quiz_id)
                        ->first();

                    if (!$existingCompletion) {
                        // Create a new completion record for the passed quiz
                        \App\Models\LessonCompletion::create([
                            'user_id' => $userId,
                            'lesson_id' => $attempt->quiz_id,
                            'lesson_title' => $attempt->quiz_title,
                            'lesson_subject' => $attempt->quiz_subject,
                            'lesson_level' => $attempt->quiz_level,
                            'lesson_level_group' => $levelGroup,
                            'watch_time_seconds' => $attempt->time_taken_seconds,
                            'total_duration_seconds' => $attempt->time_taken_seconds, // Use quiz time as duration
                            'completion_percentage' => 100.00, // Quizzes are 100% complete when passed
                            'fully_completed' => true,
                            'times_watched' => 1,
                            'first_watched_at' => $attempt->started_at ?? now(),
                            'last_watched_at' => $attempt->completed_at ?? now(),
                            'completed_at' => $attempt->completed_at ?? now(),
                        ]);
                    }
                }
            }

            Log::info('Updated lesson_completions for level progression', [
                'user_id' => $userId,
                'level_group' => $levelGroup,
                'quiz_completions_added' => $quizAttempts->where('passed', true)->count(),
                'note' => 'Skipped auto-completing video lessons to preserve watch history',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update lesson_completions for progression', [
                'user_id' => $userId,
                'level_group' => $levelGroup,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update user's grade level in the users table when they progress
     */
    private function updateUserGradeLevel($userId, $newLevelGroup)
    {
        try {
            $user = \App\Models\User::find($userId);
            if ($user) {
                // Map level group to appropriate grade level for display
                $gradeMapping = [
                    'primary-lower' => 'Primary 1-3',
                    'primary-upper' => 'Primary 4-6',
                    'jhs' => 'JHS 1-3',
                    'shs' => 'SHS 1-3',
                    'tertiary' => 'Tertiary',
                ];

                $newGrade = $gradeMapping[$newLevelGroup] ?? $newLevelGroup;

                $user->update(['grade' => $newGrade]);

                Log::info('Updated user grade level after progression', [
                    'user_id' => $userId,
                    'old_grade' => $user->getOriginal('grade'),
                    'new_grade' => $newGrade,
                    'level_group' => $newLevelGroup,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update user grade level', [
                'user_id' => $userId,
                'new_level_group' => $newLevelGroup,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Helper methods
     */
    private function getTotalLessonsForLevel($level)
    {
        // Use centralized level mapping from ProgressionStandard
        $levels = \App\Models\ProgressionStandard::getLevelsForGroup($level);

        // Get actual count from videos table
        return \App\Models\Video::whereIn('grade_level', $levels)
                               ->where('status', 'approved')
                               ->count();
    }
    
    private function getTotalQuizzesForLevel($level)
    {
        // Use centralized level mapping from ProgressionStandard
        $levels = \App\Models\ProgressionStandard::getLevelsForGroup($level);

        // Get actual count from quizzes table
        return \App\Models\Quiz::whereIn('grade_level', $levels)
                              ->count();
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

    /**
     * Refresh recent lessons and quizzes data
     */
    public function refreshRecentLessons(Request $request)
    {
        $user = auth()->user();
        $currentLevel = session('selected_level_group', 'primary-lower');

        // Get FRESH recent lessons with current progress
        $recentLessons = LessonCompletion::where('user_id', $user->id)
            ->where('lesson_level_group', $currentLevel)
            ->select(
                'id',
                'lesson_id',
                'lesson_title',
                'lesson_subject',
                'completion_percentage',
                'fully_completed',
                'last_watched_at'
            )
            ->orderBy('last_watched_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($lesson) {
                return [
                    'id' => $lesson->id,
                    'lesson_id' => $lesson->lesson_id,
                    'lesson_title' => $lesson->lesson_title,
                    'lesson_subject' => $lesson->lesson_subject,
                    'completion_percentage' => round($lesson->completion_percentage, 1),
                    'fully_completed' => $lesson->fully_completed,
                    'last_watched_at' => $lesson->last_watched_at,
                ];
            });

        // Get FRESH recent quizzes with current progress
        $recentQuizzes = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_level', $currentLevel)
            ->select(
                'id',
                'quiz_id',
                'quiz_title',
                'quiz_subject',
                'score_percentage',
                'passed',
                'completed_at'
            )
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($quiz) {
                return [
                    'id' => $quiz->id,
                    'quiz_id' => $quiz->quiz_id,
                    'quiz_title' => $quiz->quiz_title,
                    'quiz_subject' => $quiz->quiz_subject,
                    'score_percentage' => round($quiz->score_percentage, 1),
                    'passed' => $quiz->passed,
                    'completed_at' => $quiz->completed_at,
                ];
            });

        Log::info('Recent lessons/quizzes refreshed', [
            'user_id' => $user->id,
            'level_group' => $currentLevel,
            'recent_lessons_count' => $recentLessons->count(),
            'recent_quizzes_count' => $recentQuizzes->count(),
        ]);

        return response()->json([
            'success' => true,
            'recent_lessons' => $recentLessons,
            'recent_quizzes' => $recentQuizzes
        ]);
    }
}
