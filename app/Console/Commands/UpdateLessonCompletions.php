<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserProgress;
use App\Models\LessonCompletion;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Log;

class UpdateLessonCompletions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lesson-completions:update {--user_id= : Update for specific user} {--level= : Update for specific level group}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update lesson_completions table for users who meet progression thresholds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting lesson completions update...');

        $userId = $this->option('user_id');
        $levelGroup = $this->option('level');

        if ($userId && $levelGroup) {
            $this->updateSpecificUserLevel($userId, $levelGroup);
        } elseif ($userId) {
            $this->updateSpecificUser($userId);
        } elseif ($levelGroup) {
            $this->updateSpecificLevel($levelGroup);
        } else {
            $this->updateAllUsers();
        }

        $this->info('Lesson completions update completed.');
    }

    /**
     * Update lesson completions for all users
     */
    private function updateAllUsers()
    {
        $progressRecords = UserProgress::all();
        $this->info("Found {$progressRecords->count()} progress records to check");

        $bar = $this->output->createProgressBar($progressRecords->count());
        $bar->start();

        $updated = 0;
        foreach ($progressRecords as $progress) {
            if ($this->shouldUpdateLessonCompletions($progress)) {
                $this->updateLessonCompletionsForProgress($progress->user_id, $progress->level_group);
                $updated++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Updated lesson completions for {$updated} eligible progress records");
    }

    /**
     * Update lesson completions for a specific user
     */
    private function updateSpecificUser($userId)
    {
        $progressRecords = UserProgress::where('user_id', $userId)->get();
        $this->info("Found {$progressRecords->count()} progress records for user {$userId}");

        $updated = 0;
        foreach ($progressRecords as $progress) {
            if ($this->shouldUpdateLessonCompletions($progress)) {
                $this->updateLessonCompletionsForProgress($progress->user_id, $progress->level_group);
                $updated++;
                $this->info("Updated level group: {$progress->level_group}");
            }
        }

        $this->info("Updated lesson completions for {$updated} level groups for user {$userId}");
    }

    /**
     * Update lesson completions for a specific level group
     */
    private function updateSpecificLevel($levelGroup)
    {
        $progressRecords = UserProgress::where('level_group', $levelGroup)->get();
        $this->info("Found {$progressRecords->count()} progress records for level group {$levelGroup}");

        $updated = 0;
        foreach ($progressRecords as $progress) {
            if ($this->shouldUpdateLessonCompletions($progress)) {
                $this->updateLessonCompletionsForProgress($progress->user_id, $progress->level_group);
                $updated++;
                $this->info("Updated user: {$progress->user_id}");
            }
        }

        $this->info("Updated lesson completions for {$updated} users in level group {$levelGroup}");
    }

    /**
     * Update lesson completions for a specific user and level
     */
    private function updateSpecificUserLevel($userId, $levelGroup)
    {
        $progress = UserProgress::where('user_id', $userId)
                               ->where('level_group', $levelGroup)
                               ->first();

        if (!$progress) {
            $this->error("No progress record found for user {$userId} in level group {$levelGroup}");
            return;
        }

        if ($this->shouldUpdateLessonCompletions($progress)) {
            $this->updateLessonCompletionsForProgress($progress->user_id, $progress->level_group);
            $this->info("Updated lesson completions for user {$userId} in level group {$levelGroup}");
        } else {
            $this->info("User {$userId} in level group {$levelGroup} does not meet progression criteria yet");
        }
    }

    /**
     * Check if a progress record should trigger lesson completion updates
     */
    private function shouldUpdateLessonCompletions($progress)
    {
        // Get standards for this level group
        $standards = \App\Models\ProgressionStandard::getStandardsForLevel($progress->level_group);

        // Strict 100% check logic:
        // Use >= to handle rounding but conceptually it must be complete.
        $lessonCompletionRate = $progress->total_lessons_in_level > 0
            ? ($progress->completed_lessons / $progress->total_lessons_in_level) * 100
            : 0;

        $quizCompletionRate = $progress->total_quizzes_in_level > 0
            ? ($progress->completed_quizzes / $progress->total_quizzes_in_level) * 100
            : 0;

        // Ensure we handle the "100.00" string/decimal comparison correctly
        $requiredLesson = (float)$standards['required_lesson_completion_percentage'];
        $requiredQuiz = (float)$standards['required_quiz_completion_percentage'];
        $requiredScore = (float)$standards['required_average_quiz_score'];

        return $lessonCompletionRate >= $requiredLesson &&
               $quizCompletionRate >= $requiredQuiz &&
               $progress->average_quiz_score >= $requiredScore;
    }

    /**
     * Update lesson_completions table for a user who meets progression thresholds
     */
    private function updateLessonCompletionsForProgress($userId, $levelGroup)
    {
        try {
            // Get all lesson completions for this user and level group
            $lessonCompletions = LessonCompletion::where('user_id', $userId)
                ->where('lesson_level_group', $levelGroup)
                ->get();

            // Get all quiz attempts for this user and level group
            $quizAttempts = QuizAttempt::where('user_id', $userId)
                ->where('quiz_level', $levelGroup)
                ->get();

            $lessonUpdates = 0;
            $quizAdditions = 0;

            // Update lesson_completions with final completion status
            foreach ($lessonCompletions as $completion) {
                // Mark as fully completed if not already done
                if (!$completion->fully_completed) {
                    $completion->update([
                        'fully_completed' => true,
                        'completed_at' => now(),
                        'completion_percentage' => 100.00, // Mark as 100% complete for progression
                    ]);
                    $lessonUpdates++;
                }
            }

            // Create lesson completion records for quizzes that were passed
            foreach ($quizAttempts as $attempt) {
                if ($attempt->passed) {
                    // Check if we already have a completion record for this quiz
                    $existingCompletion = LessonCompletion::where('user_id', $userId)
                        ->where('lesson_id', $attempt->quiz_id)
                        ->first();

                    if (!$existingCompletion) {
                        // Create a new completion record for the passed quiz
                        LessonCompletion::create([
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
                        $quizAdditions++;
                    }
                }
            }

            // Update user's grade level in the users table
            $this->updateUserGradeLevel($userId, $levelGroup);

            if ($lessonUpdates > 0 || $quizAdditions > 0) {
                Log::info('Updated lesson_completions via command', [
                    'user_id' => $userId,
                    'level_group' => $levelGroup,
                    'lesson_completions_updated' => $lessonUpdates,
                    'quiz_completions_added' => $quizAdditions,
                ]);

                $this->line("  Updated {$lessonUpdates} lessons, added {$quizAdditions} quiz completions");
            }

        } catch (\Exception $e) {
            Log::error('Failed to update lesson_completions via command', [
                'user_id' => $userId,
                'level_group' => $levelGroup,
                'error' => $e->getMessage(),
            ]);

            $this->error("Failed to update lesson completions for user {$userId}: " . $e->getMessage());
        }
    }

    /**
     * Update user's grade level in the users table when they progress
     */
    private function updateUserGradeLevel($userId, $currentLevelGroup)
    {
        try {
            $user = \App\Models\User::find($userId);
            if (!$user) return;

            $oldGrade = $user->grade;
            $newGrade = null;

            // 1. Try to find the progress record to use its internal progression logic
            $progress = UserProgress::where('user_id', $userId)
                                   ->where('level_group', $currentLevelGroup)
                                   ->first();

            if ($progress) {
                // calls getNextLevelWithinGroup() which handles P1 -> P2
                $newGrade = $progress->shouldProgressWithinLevelGroup();
            }

            // 2. If no next level within group, find the next level group
            if (!$newGrade) {
                // Map current group to next group (hardcoded as per ProgressController)
                $groupProgression = [
                    'primary-lower' => 'primary-upper',
                    'primary-upper' => 'jhs',
                    'jhs' => 'shs',
                    'shs' => 'university',
                ];

                $nextGroupSlug = $groupProgression[$currentLevelGroup] ?? null;

                if ($nextGroupSlug) {
                    $nextGroup = \App\Models\LevelGroup::where('slug', $nextGroupSlug)->first();
                    if ($nextGroup) {
                        // Get the first level in the next group
                        $firstLevel = $nextGroup->levels()->orderBy('rank', 'asc')->first();
                        if ($firstLevel) {
                            $newGrade = $firstLevel->title;
                        }
                    }
                }
            }

            if ($newGrade && $newGrade !== $oldGrade) {
                $user->update(['grade' => $newGrade]);

                // Reset progress record if it's within group
                if ($progress) {
                     // Note: progressUserToNextLevel would normally handle record creation for group transitions
                     // But for automated command, we just ensure the grade is updated.
                }

                Log::info('Automated grade progression triggered', [
                    'user_id' => $userId,
                    'old_grade' => $oldGrade,
                    'new_grade' => $newGrade,
                    'level_group' => $currentLevelGroup,
                ]);

                $this->line("  Updated user grade from '{$oldGrade}' to '{$newGrade}'");
            }
        } catch (\Exception $e) {
            Log::error('Failed to update user grade level in command', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            $this->error("Failed to update user grade level for user {$userId}: " . $e->getMessage());
        }
    }
}