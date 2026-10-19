<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Curriculum;
use App\Models\CurriculumIndicator;
use App\Models\Textbook;
use App\Models\TextbookSection;
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

        // Check if a specific textbook or subject is requested
        $rawTextbookId = $request->input('textbook_id');
        $textbookId = $rawTextbookId ? (UrlObfuscator::decode($rawTextbookId) ?? $rawTextbookId) : null;

        $rawSubjectId = $request->input('subject_id');
        $subjectId = $rawSubjectId ? (UrlObfuscator::decode($rawSubjectId) ?? $rawSubjectId) : null;
        $isSelectingSubject = empty($subjectId) && empty($textbookId);

        // Total completed objectives across all subjects for student header
        $totalCompleted = GuidedLearningProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        if ($isSelectingSubject) {
            // 1. SUBJECT / COURSE SELECTION MODE
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

            // 1. Fetch Complete and Published Textbook Courses (Strict Quality Gate)
            $publishedCourses = $this->guidedLearningService->getPublishedTextbookCourses($user, $selectedGradeSlug);
            $publishedSubjectIds = $publishedCourses->pluck('subject_id')->filter()->unique()->toArray();

            // 2. Fallback to approved curriculum only for subjects without a complete published textbook course yet
            $curriculaQuery = Curriculum::where('is_approved', true)
                ->whereNotIn('subject_id', $publishedSubjectIds)
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

            // Load student's progress for curriculum indicators
            $userProgress = GuidedLearningProgress::where('user_id', $user->id)
                ->whereNotNull('curriculum_indicator_id')
                ->get()
                ->keyBy('curriculum_indicator_id');

            $curriculaCards = $curricula->map(function ($curr) use ($userProgress, $currentLevelGroup) {
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

                return [
                    'textbook_id' => null,
                    'encoded_textbook_id' => null,
                    'subject_id' => $curr->subject_id,
                    'encoded_subject_id' => UrlObfuscator::encode($curr->subject_id),
                    'subject_name' => $curr->subject?->name ?? $curr->title,
                    'curriculum_title' => $curr->title,
                    'tagline' => $curr->description ?? 'Official national curriculum standards and practice checkpoints.',
                    'strands_count' => $curr->strands->count(),
                    'total_indicators' => $total,
                    'completed_indicators' => $completed,
                    'in_progress_indicators' => $inProgress,
                    'percentage' => $pct,
                    'icon' => $icon,
                    'image' => $image,
                    'is_textbook_course' => false,
                ];
            });

            // Merge: Published Textbook courses take top priority!
            $subjectCards = $publishedCourses->concat($curriculaCards);

            return view('guided-learning.index', [
                'isSelectingSubject' => true,
                'subjectCards' => $subjectCards,
                'currentLevelGroup' => $currentLevelGroup,
                'levelsInGroup' => $levelsInGroup,
                'selectedGradeSlug' => $selectedGradeSlug,
                'totalCompleted' => $totalCompleted,
            ]);
        }

        // 2. COURSE ROADMAP MODE (Textbook or Subject is selected)
        $publishedTextbook = null;
        if ($textbookId) {
            $publishedTextbook = Textbook::with(['subject', 'level', 'levelGroup'])->find($textbookId);
            $currentSubject = $publishedTextbook?->subject ?? Subject::first();
        } else {
            $currentSubject = Subject::findOrFail($subjectId);
            $publishedTextbook = $this->guidedLearningService->getPublishedTextbookForSubject($user, $currentSubject);
        }

        if ($publishedTextbook) {
            $curriculumData = $this->guidedLearningService->getTextbookCourseTreeForStudent($user, $publishedTextbook);
        } else {
            $curriculumData = $this->guidedLearningService->getCurriculumTreeForStudent($user, $currentSubject);
        }

        $availableSubjects = Subject::where(function ($q) {
            $q->whereHas('curricula', function ($cq) {
                $cq->where('is_approved', true);
            })->orWhereHas('textbooks', function ($tq) {
                $tq->where('is_content_approved', true);
            });
        })->orderBy('name')->get();

        return view('guided-learning.index', [
            'isSelectingSubject' => false,
            'currentSubject' => $currentSubject,
            'publishedTextbook' => $publishedTextbook,
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
                    'requires_interactive' => true,
                    'message' => 'Please complete all interactive challenges before marking this topic as completed.',
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
        $isReadingCompleted = $request->boolean('reading_completed');

        $segments = app(\App\Services\StudentLessonService::class)->getOrGenerateSegments($indicatorModel);
        $targetSegment = collect($segments)->firstWhere('id', $segmentId);

        if (!$targetSegment) {
            return response()->json([
                'success' => false,
                'message' => 'Segment not found.',
            ], 404);
        }

        $totalSegments = count($segments);
        $completedSegments = (array) $request->input('completed_segments', []);

        // If this segment has no challenge (reading/intuition only) or was acknowledged directly
        if (empty($targetSegment['challenge']) || $isReadingCompleted) {
            if (!in_array($segmentId, $completedSegments)) {
                $completedSegments[] = $segmentId;
            }
            $allSegmentsCompleted = count($completedSegments) >= $totalSegments;
            if ($allSegmentsCompleted && $user) {
                $progress = GuidedLearningProgress::firstOrCreate(
                    ['user_id' => $user->id, 'curriculum_indicator_id' => $indicatorModel->id],
                    ['status' => 'in_progress']
                );
                $progress->status = 'completed';
                $progress->completed_at = now();
                $progress->quiz_passed_at = now();
                $progress->quiz_score = 100;
                $progress->save();
            }

            return response()->json([
                'success' => true,
                'is_correct' => true,
                'explanation' => 'Section completed successfully.',
                'all_completed' => $allSegmentsCompleted,
            ]);
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
        if ($allSegmentsCompleted && $user) {
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

    /**
     * Display the full topic reader for a specific textbook section.
     */
    public function section(string|int $section)
    {
        $user = Auth::user();
        $sectionId = UrlObfuscator::decode($section) ?? $section;
        $learningPackage = $this->guidedLearningService->getSectionLearningPackage((int)$sectionId, $user);

        return view('guided-learning.topic', $learningPackage);
    }

    /**
     * Toggle textbook section completion status (FreeCodeCamp checkbox click).
     */
    public function toggleSection(Request $request, string|int $section): JsonResponse
    {
        $user = Auth::user();
        $sectionId = UrlObfuscator::decode($section) ?? $section;
        $sectionModel = TextbookSection::findOrFail($sectionId);
        $explicitStatus = $request->input('status');

        if ($explicitStatus === 'completed' && !empty($sectionModel->lesson_quiz)) {
            $existingProgress = GuidedLearningProgress::where('user_id', $user->id)
                ->where('textbook_section_id', $sectionModel->id)
                ->first();

            $hasPassedQuiz = $existingProgress && ($existingProgress->quiz_passed_at !== null || $existingProgress->status === 'completed');

            if (!$hasPassedQuiz) {
                return response()->json([
                    'success' => false,
                    'requires_interactive' => true,
                    'message' => 'Please complete all checkpoint challenges before marking this topic as completed.',
                ], 422);
            }
        }

        $progress = $this->guidedLearningService->toggleSectionStatus($user, $sectionModel->id, $explicitStatus);

        $textbook = $sectionModel->chapter->textbook;
        $tree = $this->guidedLearningService->getTextbookCourseTreeForStudent($user, $textbook);

        return response()->json([
            'success' => true,
            'section_id' => $sectionModel->id,
            'encoded_section_id' => UrlObfuscator::encode($sectionModel->id),
            'status' => $progress->status,
            'completed_at' => $progress->completed_at?->format('M d, Y'),
            'stats' => $tree['stats'] ?? null,
        ]);
    }

    /**
     * Submit and evaluate the checkpoint quiz for a textbook section.
     */
    public function submitSectionQuiz(Request $request, string|int $section): JsonResponse
    {
        $user = Auth::user();
        $sectionId = UrlObfuscator::decode($section) ?? $section;
        $sectionModel = TextbookSection::findOrFail($sectionId);

        $answers = $request->input('answers', []);
        $serverQuiz = $sectionModel->lesson_quiz ?? [];
        if (is_string($serverQuiz)) {
            $serverQuiz = json_decode($serverQuiz, true) ?? [];
        }

        $totalQuestions = count($serverQuiz);
        if ($totalQuestions === 0) {
            $progress = $this->guidedLearningService->toggleSectionStatus($user, $sectionModel->id, 'completed');
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
        $passed = ($correctCount === $totalQuestions);

        $progress = GuidedLearningProgress::firstOrCreate(
            ['user_id' => $user->id, 'textbook_section_id' => $sectionModel->id],
            ['status' => 'in_progress']
        );

        $progress->quiz_attempts = ($progress->quiz_attempts ?? 0) + 1;
        $progress->quiz_score = max($progress->quiz_score ?? 0, $percentage);

        if ($passed) {
            $progress->quiz_passed_at = now();
            $progress->status = 'completed';
            $progress->completed_at = now();
        }
        $progress->save();

        $textbook = $sectionModel->chapter->textbook;
        $tree = $this->guidedLearningService->getTextbookCourseTreeForStudent($user, $textbook);

        // Next section URL
        $allSections = TextbookSection::whereHas('chapter', function ($q) use ($textbook) {
            $q->where('textbook_id', $textbook->id);
        })->join('textbook_chapters', 'textbook_sections.chapter_id', '=', 'textbook_chapters.id')
          ->orderBy('textbook_chapters.sort_order')
          ->orderBy('textbook_sections.sort_order')
          ->select('textbook_sections.id')
          ->pluck('id')
          ->toArray();

        $currentIndex = array_search($sectionModel->id, $allSections);
        $nextUrl = null;
        if ($currentIndex !== false && $currentIndex < count($allSections) - 1) {
            $nextUrl = route('guided-learning.section', UrlObfuscator::encode($allSections[$currentIndex + 1]));
        } else {
            $nextUrl = route('guided-learning.index', [
                'textbook_id' => UrlObfuscator::encode($textbook->id),
                'subject_id' => UrlObfuscator::encode($textbook->subject_id),
            ]);
        }

        return response()->json([
            'success' => true,
            'passed' => $passed,
            'score' => $percentage,
            'correct_count' => $correctCount,
            'total' => $totalQuestions,
            'results' => $questionResults,
            'status' => $progress->status,
            'stats' => $tree['stats'] ?? null,
            'next_url' => $nextUrl,
            'message' => $passed
                ? 'Mastery Achieved! Checkpoint 100% completed.'
                : "You got {$correctCount} of {$totalQuestions} correct. Score 100% to complete this topic.",
        ]);
    }
}
