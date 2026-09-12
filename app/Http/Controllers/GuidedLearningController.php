<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Curriculum;
use App\Models\CurriculumIndicator;
use App\Models\GuidedLearningProgress;
use App\Services\GuidedLearningService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GuidedLearningController extends Controller
{
    public function __construct(protected GuidedLearningService $guidedLearningService)
    {
    }

    /**
     * Display the FreeCodeCamp-style guided learning tree.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get available subjects with approved curricula
        $availableSubjects = Subject::whereHas('curricula', function ($q) {
            $q->where('is_approved', true);
        })->orderBy('name')->get();

        if ($availableSubjects->isEmpty()) {
            // Fallback to all subjects if none marked approved yet
            $availableSubjects = Subject::orderBy('name')->get();
        }

        $selectedSubjectId = $request->input('subject_id', $availableSubjects->first()?->id);
        $currentSubject = Subject::find($selectedSubjectId) ?? $availableSubjects->first();

        $curriculumData = null;
        if ($currentSubject) {
            $curriculumData = $this->guidedLearningService->getCurriculumTreeForStudent($user, $currentSubject);
        }

        // Global user stats across all subjects
        $totalCompleted = GuidedLearningProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        return view('guided-learning.index', compact(
            'availableSubjects',
            'currentSubject',
            'curriculumData',
            'totalCompleted'
        ));
    }

    /**
     * Display the full topic reader for a specific indicator.
     */
    public function topic(CurriculumIndicator $indicator)
    {
        $user = Auth::user();
        $learningPackage = $this->guidedLearningService->getIndicatorLearningPackage($indicator->id, $user);

        return view('guided-learning.topic', $learningPackage);
    }

    /**
     * Toggle indicator progress status (FreeCodeCamp checkbox click).
     */
    public function toggleStatus(Request $request, CurriculumIndicator $indicator): JsonResponse
    {
        $user = Auth::user();
        $explicitStatus = $request->input('status'); // optional: not_started, in_progress, completed

        $progress = $this->guidedLearningService->toggleIndicatorStatus($user, $indicator->id, $explicitStatus);

        // Fetch refreshed stats for this subject
        $subject = $indicator->subStrand->strand->curriculum->subject;
        $tree = $this->guidedLearningService->getCurriculumTreeForStudent($user, $subject);

        return response()->json([
            'success' => true,
            'indicator_id' => $indicator->id,
            'status' => $progress->status,
            'completed_at' => $progress->completed_at?->format('M d, Y'),
            'stats' => $tree['stats'] ?? null,
        ]);
    }

    /**
     * Save student personal notes for an indicator.
     */
    public function saveNotes(Request $request, CurriculumIndicator $indicator): JsonResponse
    {
        $user = Auth::user();
        $notes = $request->input('notes');

        $progress = GuidedLearningProgress::firstOrCreate(
            ['user_id' => $user->id, 'curriculum_indicator_id' => $indicator->id],
            ['status' => 'in_progress']
        );

        $progress->update(['notes' => $notes]);

        return response()->json(['success' => true, 'notes' => $notes]);
    }
}
