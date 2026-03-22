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
        $attempt = QuizAttempt::with(['user', 'quiz'])->findOrFail($id);
        
        // Fetch all violations for this user and quiz within a reasonable time window
        // Or simply matching the quiz_id and user_id since attempts are usually sequential
        $violations = QuizViolation::where('user_id', $attempt->user_id)
            ->where('quiz_id', $attempt->quiz_id)
            ->whereBetween('occurred_at', [
                $attempt->started_at->subMinutes(5), 
                $attempt->completed_at ? $attempt->completed_at->addMinutes(5) : now()
            ])
            ->orderBy('occurred_at', 'asc')
            ->get();

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
        QuizViolation::recordViolation(
            $attempt->user_id,
            $attempt->quiz_id,
            'manual_invalidation',
            'This attempt was manually invalidated by an administrator after review.'
        );

        return back()->with('success', 'Attempt has been invalidated successfully.');
    }
}
