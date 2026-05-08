<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt;
use App\Models\QuizViolation;
use Illuminate\Http\Request;

class QuizReviewController extends Controller
{
    /**
     * Display a listing of quiz attempts with violation flags.
     */
    public function index(Request $request)
    {
        $query = QuizAttempt::with(['user', 'quiz'])
            ->orderBy('completed_at', 'desc');

        // Filter by user search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by violation status
        if ($request->has('violation')) {
            if ($request->input('violation') === 'yes') {
                $query->where('failed_due_to_violation', true)
                      ->orWhereHas('quiz', function($q) {
                          // This is a bit complex, we'll use a subquery for violation count in the view
                      });
            }
        }

        $attempts = $query->paginate(20);

        return view('admin.quizzes.review.index', compact('attempts'));
    }

    /**
     * Display the specified quiz attempt with full violation details.
     */
    public function show($id)
    {
        $attempt = QuizAttempt::with(['user', 'quiz', 'violations' => function($query) {
            $query->orderBy('occurred_at', 'asc');
        }])->findOrFail($id);
        
        $violations = $attempt->violations;

        return view('admin.quizzes.review.show', compact('attempt', 'violations'));
    }

    /**
     * Invalidate an attempt manually.
     */
    public function invalidate(Request $request, $id)
    {
        $attempt = QuizAttempt::findOrFail($id);
        $attempt->update([
            'failed_due_to_violation' => true,
            'correct_answers' => 0,
            'score_percentage' => 0,
            'passed' => false,
        ]);

        // Record the manual invalidation as a violation
        \App\Models\QuizViolation::recordViolation(
            $attempt->user_id,
            $attempt->quiz_id,
            'manual_invalidation',
            'This attempt was manually invalidated by an administrator after review.',
            10, // Max severity
            $attempt->id
        );

        return back()->with('success', 'Attempt has been invalidated successfully.');
    }

    /**
     * Grade an essay quiz attempt manually.
     */
    public function grade(Request $request, $id)
    {
        $attempt = QuizAttempt::findOrFail($id);
        
        $request->validate([
            'marks' => 'required|array',
            'feedback' => 'nullable|array',
            'overall_feedback' => 'nullable|string',
        ]);

        $marksAwarded = $request->input('marks');
        $feedback = $request->input('feedback');
        
        // Calculate total score
        $totalEarned = 0;
        foreach($marksAwarded as $mark) {
            $totalEarned += (float)$mark;
        }
        
        // Fetch total possible points from quiz data stored in attempt
        $totalPossible = 0;
        $questionDetails = is_array($attempt->question_details) ? $attempt->question_details : [];
        
        foreach($questionDetails as $q) {
            // For older structures or MCQ, check points
            if (empty($q['sub_questions'])) {
                $totalPossible += ($q['points'] ?? 1);
            } else {
                foreach($q['sub_questions'] as $sub) {
                    $totalPossible += ($sub['points'] ?? 1);
                }
            }
        }

        // Avoid division by zero
        $percentage = $totalPossible > 0 ? ($totalEarned / $totalPossible) * 100 : 0;
        
        // Determine passing threshold
        $levelGroup = \App\Models\ProgressionStandard::getLevelGroup($attempt->quiz_level);
        $standards = \App\Models\ProgressionStandard::getStandardsForLevel($levelGroup);
        $passingThreshold = $standards['minimum_quiz_score'] ?? 50.00;

        $passed = $percentage >= $passingThreshold;

        $attempt->update([
            'correct_answers' => (int)$totalEarned,
            'score_percentage' => $percentage,
            'passed' => $passed,
            'status' => 'graded',
            'grading_details' => [
                'marks' => $marksAwarded,
                'feedback' => $feedback,
                'overall_feedback' => $request->input('overall_feedback'),
                'total_possible' => $totalPossible,
                'total_earned' => $totalEarned
            ],
            'graded_by' => \Illuminate\Support\Facades\Auth::user()->name,
            'graded_at' => now(),
        ]);

        return back()->with('success', 'Quiz has been graded successfully.');
    }

    /**
     * Run automated grading for an attempt.
     */
    public function autoGrade(Request $request, $id)
    {
        $attempt = QuizAttempt::findOrFail($id);
        $service = new \App\Services\Quiz\QuizAutomatedGradingService();

        // Check if rate-limited before even trying
        if ($service->isGeminiRateLimited()) {
            $cooldown = $service->geminiCooldownRemaining();
            return response()->json([
                'error' => 'rate_limited',
                'message' => "AI grading is temporarily unavailable due to API rate limits. Please try again in {$cooldown} seconds, or grade manually.",
                'cooldown_seconds' => $cooldown,
            ], 429);
        }

        try {
            $suggestions = $service->suggestMarks($attempt);
            $suggestions['grading_driver'] = $service->getGradingDriver();
            return response()->json($suggestions);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Auto-grade failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'grading_failed',
                'message' => 'AI grading encountered an error. Please grade manually or try again later.',
            ], 500);
        }
    }
}
