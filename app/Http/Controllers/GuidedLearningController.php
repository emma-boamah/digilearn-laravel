<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Curriculum;
use App\Models\CurriculumIndicator;
use App\Models\GuidedLearningProgress;
use App\Services\GuidedLearningService;
use App\Services\UrlObfuscator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GuidedLearningController extends Controller
{
    public function __construct(protected GuidedLearningService $guidedLearningService)
    {
    }

    /**
     * Display the FreeCodeCamp-style guided learning tree or subject selection.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if a specific subject is requested (support obfuscated or raw ID)
        $rawSubjectId = $request->input('subject_id');
        $subjectId = $rawSubjectId ? (UrlObfuscator::decode($rawSubjectId) ?? $rawSubjectId) : null;
        $isSelectingSubject = empty($subjectId);

        // Total completed objectives across all subjects for student header
        $totalCompleted = GuidedLearningProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        if ($isSelectingSubject) {
            // 1. SUBJECT SELECTION MODE
            $selectedLevelGroupSlug = session('selected_level_group') ?? $user->current_level_group ?? 'jhs';
            
            $currentLevelGroup = \App\Models\LevelGroup::where('slug', $selectedLevelGroupSlug)
                ->orWhere('title', 'like', "%{$selectedLevelGroupSlug}%")
                ->with(['levels' => function ($q) {
                    $q->orderBy('rank');
                }])
                ->first() ?? \App\Models\LevelGroup::where('slug', 'jhs')->with(['levels' => function ($q) {
                    $q->orderBy('rank');
                }])->first();

            $levelsInGroup = $currentLevelGroup ? $currentLevelGroup->levels : collect();
            $selectedGradeSlug = $request->input('grade', 'all');

            $curriculaQuery = Curriculum::where('is_approved', true)
                ->with(['subject', 'level', 'strands.subStrands.indicators']);

            if ($currentLevelGroup) {
                $hasGroupCurricula = (clone $curriculaQuery)->where('level_group_id', $currentLevelGroup->id)->exists();
                if ($hasGroupCurricula) {
                    $curriculaQuery->where('level_group_id', $currentLevelGroup->id);
                }
            }

            // Grade filter within the chosen level group
            if ($selectedGradeSlug !== 'all') {
                $matchedLevel = $levelsInGroup->firstWhere('slug', $selectedGradeSlug);
                if ($matchedLevel) {
                    $hasGradeCurricula = (clone $curriculaQuery)->where('level_id', $matchedLevel->id)->exists();
                    if ($hasGradeCurricula) {
                        $curriculaQuery->where('level_id', $matchedLevel->id);
                    }
                }
            }

            $curricula = $curriculaQuery->get();

            // Load student's progress
            $userProgress = GuidedLearningProgress::where('user_id', $user->id)
                ->get()
                ->keyBy('curriculum_indicator_id');

            $subjectCards = $curricula->map(function ($curr) use ($userProgress) {
                $indicatorIds = $curr->strands->flatMap(function ($s) {
                    return $s->subStrands->flatMap(function ($ss) {
                        return $ss->indicators->pluck('id');
                    });
                })->toArray();

                $total = count($indicatorIds);
                $completed = 0;
                $inProgress = 0;
                foreach ($indicatorIds as $id) {
                    $status = $userProgress->get($id)?->status ?? 'not_started';
                    if ($status === 'completed') {
                        $completed++;
                    } elseif ($status === 'in_progress') {
                        $inProgress++;
                    }
                }

                $pct = $total > 0 ? round(($completed / $total) * 100) : 0;

                $sName = strtolower($curr->subject?->name ?? '');
                $isMath = str_contains($sName, 'math');

                $image = null;
                if ($isMath) {
                    $lgSlug = strtolower(
                        $curr->levelGroup?->slug 
                        ?? ($curr->level?->levelGroup?->slug 
                        ?? ($currentLevelGroup?->slug ?? ''))
                    );

                    if (str_contains($lgSlug, 'primary')) {
                        $image = asset('images/guided-learning/math/math-primary.jpg');
                    } elseif (str_contains($lgSlug, 'jhs')) {
                        $image = asset('images/guided-learning/math/math-jhs.jpg');
                    } else {
                        $image = asset('images/guided-learning/math/math-default.png');
                    }
                }

                $icon = match (true) {
                    str_contains($sName, 'math') => 'fa-calculator',
                    str_contains($sName, 'english') || str_contains($sName, 'literacy') || str_contains($sName, 'language') => 'fa-book-open',
                    str_contains($sName, 'science') => 'fa-flask',
                    str_contains($sName, 'comput') || str_contains($sName, 'ict') || str_contains($sName, 'tech') => 'fa-laptop-code',
                    str_contains($sName, 'social') || str_contains($sName, 'history') || str_contains($sName, 'geography') => 'fa-globe-africa',
                    default => 'fa-graduation-cap',
                };

                $tagline = match (true) {
                    str_contains($sName, 'math') => 'Number systems, algebra, geometry, and problem solving.',
                    str_contains($sName, 'english') || str_contains($sName, 'literacy') || str_contains($sName, 'language') => 'Grammar, reading comprehension, writing, and vocabulary.',
                    str_contains($sName, 'science') => 'Living systems, matter, energy, ecosystems, and earth.',
                    str_contains($sName, 'comput') || str_contains($sName, 'ict') || str_contains($sName, 'tech') => 'Hardware, programming concepts, digital literacy, and online safety.',
                    default => $curr->description ?? 'Official national curriculum standards and practice checkpoints.',
                };

                return [
                    'subject_id' => $curr->subject_id,
                    'encoded_subject_id' => UrlObfuscator::encode($curr->subject_id),
                    'subject_name' => $curr->subject?->name ?? $curr->title,
                    'curriculum_title' => $curr->title,
                    'tagline' => $tagline,
                    'strands_count' => $curr->strands->count(),
                    'total_indicators' => $total,
                    'completed_indicators' => $completed,
                    'in_progress_indicators' => $inProgress,
                    'percentage' => $pct,
                    'icon' => $icon,
                    'image' => $image,
                ];
            });

            return view('guided-learning.index', [
                'isSelectingSubject' => true,
                'subjectCards' => $subjectCards,
                'currentLevelGroup' => $currentLevelGroup,
                'levelsInGroup' => $levelsInGroup,
                'selectedGradeSlug' => $selectedGradeSlug,
                'totalCompleted' => $totalCompleted,
            ]);
        }

        // 2. INDICATORS / ROADMAP MODE (Subject is selected)
        $currentSubject = Subject::findOrFail($subjectId);
        $curriculumData = $this->guidedLearningService->getCurriculumTreeForStudent($user, $currentSubject);

        $availableSubjects = Subject::whereHas('curricula', function ($q) {
            $q->where('is_approved', true);
        })->orderBy('name')->get();

        return view('guided-learning.index', [
            'isSelectingSubject' => false,
            'currentSubject' => $currentSubject,
            'encodedSubjectId' => UrlObfuscator::encode($currentSubject->id),
            'curriculumData' => $curriculumData,
            'availableSubjects' => $availableSubjects,
            'totalCompleted' => $totalCompleted,
        ]);
    }

    /**
     * Display the full topic reader for a specific indicator.
     */
    public function topic(string|int $indicator)
    {
        $user = Auth::user();
        $indicatorId = UrlObfuscator::decode($indicator) ?? $indicator;
        $indicatorModel = CurriculumIndicator::findOrFail($indicatorId);
        $learningPackage = $this->guidedLearningService->getIndicatorLearningPackage($indicatorModel->id, $user);

        return view('guided-learning.topic', $learningPackage);
    }

    /**
     * Toggle indicator progress status (FreeCodeCamp checkbox click).
     */
    public function toggleStatus(Request $request, string|int $indicator): JsonResponse
    {
        $user = Auth::user();
        $indicatorId = UrlObfuscator::decode($indicator) ?? $indicator;
        $indicatorModel = CurriculumIndicator::findOrFail($indicatorId);
        $explicitStatus = $request->input('status'); // optional: not_started, in_progress, completed

        $progress = $this->guidedLearningService->toggleIndicatorStatus($user, $indicatorModel->id, $explicitStatus);

        // Fetch refreshed stats for this subject
        $subject = $indicatorModel->subStrand->strand->curriculum->subject;
        $tree = $this->guidedLearningService->getCurriculumTreeForStudent($user, $subject);

        return response()->json([
            'success' => true,
            'indicator_id' => $indicatorModel->id,
            'encoded_indicator_id' => UrlObfuscator::encode($indicatorModel->id),
            'status' => $progress->status,
            'completed_at' => $progress->completed_at?->format('M d, Y'),
            'stats' => $tree['stats'] ?? null,
        ]);
    }

    /**
     * Save student personal notes for an indicator.
     */
    public function saveNotes(Request $request, string|int $indicator): JsonResponse
    {
        $user = Auth::user();
        $indicatorId = UrlObfuscator::decode($indicator) ?? $indicator;
        $indicatorModel = CurriculumIndicator::findOrFail($indicatorId);
        $notes = $request->input('notes');

        $progress = GuidedLearningProgress::firstOrCreate(
            ['user_id' => $user->id, 'curriculum_indicator_id' => $indicatorModel->id],
            ['status' => 'in_progress']
        );

        $progress->update(['notes' => $notes]);

        return response()->json(['success' => true, 'notes' => $notes]);
    }
}
