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

        // COMPREHENSION GATE: If student attempts to mark as completed directly, check if quiz was passed
        if ($explicitStatus === 'completed') {
            $existingProgress = GuidedLearningProgress::where('user_id', $user->id)
                ->where('curriculum_indicator_id', $indicatorModel->id)
                ->first();

            // Allow if previously passed, or if existing completed record exists (retroactive compatibility)
            $hasPassedQuiz = $existingProgress && ($existingProgress->quiz_passed_at !== null || $existingProgress->status === 'completed');

            if (!$hasPassedQuiz) {
                return response()->json([
                    'success' => false,
                    'requires_quiz' => true,
                    'message' => 'Please complete and pass the Checkpoint Quiz before marking this topic as completed.',
                ], 422);
            }
        }

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
     * Submit and evaluate the topic checkpoint quiz.
     * All answers must be correct (100%) to complete the topic.
     */
    public function submitQuiz(Request $request, string|int $indicator): JsonResponse
    {
        $user = Auth::user();
        $indicatorId = UrlObfuscator::decode($indicator) ?? $indicator;
        $indicatorModel = CurriculumIndicator::findOrFail($indicatorId);

        $answers = $request->input('answers', []); // map of question_id => selected_option_index

        // Retrieve server-side truth quiz
        $serverQuiz = app(\App\Services\StudentLessonService::class)->getOrGenerateQuiz($indicatorModel);

        $totalQuestions = count($serverQuiz);
        if ($totalQuestions === 0) {
            // Edge case: No questions exist, auto-pass
            $progress = $this->guidedLearningService->toggleIndicatorStatus($user, $indicatorModel->id, 'completed');
            return response()->json([
                'success' => true,
                'passed' => true,
                'score' => 100,
                'total' => 0,
                'correct_count' => 0,
            ]);
        }

        $correctCount = 0;
        $questionResults = [];

        foreach ($serverQuiz as $q) {
            $qId = (string) ($q['id'] ?? 0);
            $userAns = isset($answers[$qId]) ? (int) $answers[$qId] : null;
            $correctAns = (int) ($q['correct_answer'] ?? 0);
            $isCorrect = ($userAns !== null && $userAns === $correctAns);

            if ($isCorrect) {
                $correctCount++;
            }

            $questionResults[] = [
                'id' => $q['id'],
                'user_answer' => $userAns,
                'is_correct' => $isCorrect,
                'correct_answer' => $correctAns,
                'explanation' => $q['explanation'] ?? '',
            ];
        }

        $percentage = round(($correctCount / $totalQuestions) * 100);
        $passed = ($correctCount === $totalQuestions); // 100% required

        // Update student progress
        $progress = GuidedLearningProgress::firstOrCreate(
            ['user_id' => $user->id, 'curriculum_indicator_id' => $indicatorModel->id],
            ['status' => 'in_progress']
        );

        $progress->quiz_attempts = ($progress->quiz_attempts ?? 0) + 1;
        $progress->quiz_score = max($progress->quiz_score ?? 0, (int) $percentage);

        if ($passed) {
            $progress->status = 'completed';
            $progress->completed_at = now();
            $progress->quiz_passed_at = now();
        }

        $progress->save();

        // Calculate next indicator URL if passed
        $nextUrl = null;
        if ($passed) {
            $curriculum = $indicatorModel->subStrand?->strand?->curriculum;
            if ($curriculum) {
                $allIndicators = CurriculumIndicator::whereHas('subStrand.strand', function ($q) use ($curriculum) {
                    $q->where('curriculum_id', $curriculum->id);
                })->orderBy('id')->pluck('id')->toArray();

                $currentIndex = array_search($indicatorModel->id, $allIndicators);
                if ($currentIndex !== false && $currentIndex < count($allIndicators) - 1) {
                    $nextIndicatorId = $allIndicators[$currentIndex + 1];
                    $nextUrl = route('guided-learning.topic', UrlObfuscator::encode($nextIndicatorId));
                } else {
                    $nextUrl = route('guided-learning.index', [
                        'subject_id' => UrlObfuscator::encode($curriculum->subject_id ?? 1)
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'passed' => $passed,
            'score' => $percentage,
            'total' => $totalQuestions,
            'correct_count' => $correctCount,
            'attempts' => $progress->quiz_attempts,
            'results' => $questionResults,
            'next_url' => $nextUrl,
            'status' => $progress->status,
        ]);
    }

    /**
     * Verify student answer for an interleaved interactive segment challenge.
     * Evaluates server-side, returns feedback/hint, and updates progression.
     */
    public function verifySegment(Request $request, string|int $indicator): JsonResponse
    {
        $user = Auth::user();
        $indicatorId = UrlObfuscator::decode($indicator) ?? $indicator;
        $indicatorModel = CurriculumIndicator::findOrFail($indicatorId);

        $segmentId = (int) $request->input('segment_id');
        $studentAnswer = $request->input('answer');

        $segments = app(\App\Services\StudentLessonService::class)->getOrGenerateSegments($indicatorModel);
        $targetSegment = collect($segments)->firstWhere('id', $segmentId);

        if (!$targetSegment || empty($targetSegment['challenge'])) {
            return response()->json([
                'success' => false,
                'message' => 'Segment challenge not found.',
            ], 404);
        }

        $challenge = $targetSegment['challenge'];
        $type = $challenge['type'] ?? 'multiple-choice';
        $correctAnswer = $challenge['correct_answer'] ?? 0;
        $isCorrect = false;

        if ($type === 'multiple-choice') {
            $isCorrect = ((int) $studentAnswer === (int) $correctAnswer);
        } else {
            // Numeric or text input matching (cleans commas, whitespace, dollar signs)
            $cleanStudent = strtolower(trim(str_replace([',', '$', ' '], '', (string) $studentAnswer)));
            $cleanCorrect = strtolower(trim(str_replace([',', '$', ' '], '', (string) $correctAnswer)));
            $isCorrect = ($cleanStudent === $cleanCorrect);
        }

        // Check if this was the last segment in the indicator
        $totalSegments = count($segments);
        $completedSegments = (array) $request->input('completed_segments', []);
        if ($isCorrect && !in_array($segmentId, $completedSegments)) {
            $completedSegments[] = $segmentId;
        }

        $allSegmentsCompleted = count($completedSegments) >= $totalSegments;

        // If all segments solved, mark progress completed!
        $nextUrl = null;
        if ($allSegmentsCompleted) {
            $progress = GuidedLearningProgress::firstOrCreate(
                ['user_id' => $user->id, 'curriculum_indicator_id' => $indicatorModel->id],
                ['status' => 'in_progress']
            );

            $progress->status = 'completed';
            $progress->completed_at = now();
            $progress->quiz_passed_at = now();
            $progress->quiz_score = 100;
            $progress->save();

            // Next topic URL
            $curriculum = $indicatorModel->subStrand?->strand?->curriculum;
            if ($curriculum) {
                $allIndicators = CurriculumIndicator::whereHas('subStrand.strand', function ($q) use ($curriculum) {
                    $q->where('curriculum_id', $curriculum->id);
                })->orderBy('id')->pluck('id')->toArray();

                $currentIndex = array_search($indicatorModel->id, $allIndicators);
                if ($currentIndex !== false && $currentIndex < count($allIndicators) - 1) {
                    $nextIndicatorId = $allIndicators[$currentIndex + 1];
                    $nextUrl = route('guided-learning.topic', UrlObfuscator::encode($nextIndicatorId));
                } else {
                    $nextUrl = route('guided-learning.index', [
                        'subject_id' => UrlObfuscator::encode($curriculum->subject_id ?? 1)
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'is_correct' => $isCorrect,
            'correct_answer' => $correctAnswer,
            'explanation' => $challenge['explanation'] ?? '',
            'hint' => $challenge['hint'] ?? '',
            'segment_id' => $segmentId,
            'completed_segments' => $completedSegments,
            'all_completed' => $allSegmentsCompleted,
            'next_url' => $nextUrl,
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
