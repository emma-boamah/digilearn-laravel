<?php

namespace App\Services;

use App\Models\Curriculum;
use App\Models\CurriculumIndicator;
use App\Models\GuidedLearningProgress;
use App\Models\Subject;
use App\Models\Textbook;
use App\Models\TextbookChapter;
use App\Models\TextbookSection;
use App\Models\User;
use App\Models\Video;
use App\Models\Document;
use App\Models\Quiz;
use App\Services\UrlObfuscator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GuidedLearningService
{
    /**
     * Get published and complete textbook courses available for the student.
     */
    public function getPublishedTextbookCourses(User $user, ?string $gradeSlug = null): \Illuminate\Support\Collection
    {
        $selectedLevelGroupSlug = session('selected_level_group') ?? $user->current_level_group ?? 'jhs';

        $baseQuery = Textbook::with([
            'subject',
            'level.levelGroup',
            'levelGroup',
            'chapters.sections',
        ]);

        $query = clone $baseQuery;

        // Filter by level group if textbooks exist in that group
        if ($selectedLevelGroupSlug) {
            $hasGroupTextbooks = (clone $query)->where(function ($q) use ($selectedLevelGroupSlug) {
                $q->whereHas('levelGroup', function ($lq) use ($selectedLevelGroupSlug) {
                    $lq->where('slug', $selectedLevelGroupSlug)
                       ->orWhere('title', 'like', "%{$selectedLevelGroupSlug}%");
                })->orWhereHas('level.levelGroup', function ($lq) use ($selectedLevelGroupSlug) {
                    $lq->where('slug', $selectedLevelGroupSlug)
                       ->orWhere('title', 'like', "%{$selectedLevelGroupSlug}%");
                });
            })->exists();

            if ($hasGroupTextbooks) {
                $query->where(function ($q) use ($selectedLevelGroupSlug) {
                    $q->whereHas('levelGroup', function ($lq) use ($selectedLevelGroupSlug) {
                        $lq->where('slug', $selectedLevelGroupSlug)
                           ->orWhere('title', 'like', "%{$selectedLevelGroupSlug}%");
                    })->orWhereHas('level.levelGroup', function ($lq) use ($selectedLevelGroupSlug) {
                        $lq->where('slug', $selectedLevelGroupSlug)
                           ->orWhere('title', 'like', "%{$selectedLevelGroupSlug}%");
                    });
                });
            }
        }

        // Filter by grade if specified and not 'all'
        if ($gradeSlug && $gradeSlug !== 'all') {
            $hasGradeTextbooks = (clone $query)->whereHas('level', function ($lq) use ($gradeSlug) {
                $lq->where('slug', $gradeSlug)
                   ->orWhere('title', 'like', "%{$gradeSlug}%");
            })->exists();

            if ($hasGradeTextbooks) {
                $query->whereHas('level', function ($lq) use ($gradeSlug) {
                    $lq->where('slug', $gradeSlug)
                       ->orWhere('title', 'like', "%{$gradeSlug}%");
                });
            }
        }

        $allTextbooks = $query->get();

        // Filter eligible textbook courses:
        // 1. Explicitly marked as active course by admin (with chapters), OR
        // 2. Complete and published (all sections ready)
        $eligibleTextbooks = $allTextbooks->filter(function (Textbook $tb) {
            if ($tb->is_course_active && $tb->chapters->count() > 0) {
                return true;
            }
            return $tb->isCourseComplete();
        });

        // If no eligible textbooks found in current filtered query, fallback to any active textbook courses!
        if ($eligibleTextbooks->isEmpty()) {
            $eligibleTextbooks = (clone $baseQuery)->where('is_course_active', true)->whereHas('chapters')->get();
        }

        // Group by subject_id so each subject displays only ONE authoritative course!
        // Priority: 1. is_course_active = true, 2. First complete
        $selectedTextbooks = $eligibleTextbooks->groupBy('subject_id')->map(function ($group) {
            $active = $group->firstWhere('is_course_active', true);
            return $active ?: $group->first();
        })->values();

        // Also check if any other subjects have an active textbook course that should be available
        $foundSubjectIds = $selectedTextbooks->pluck('subject_id')->filter()->unique()->toArray();
        $fallbackActiveTextbooks = (clone $baseQuery)
            ->where('is_course_active', true)
            ->whereHas('chapters')
            ->whereNotIn('subject_id', $foundSubjectIds)
            ->get();

        if ($fallbackActiveTextbooks->isNotEmpty()) {
            $selectedTextbooks = $selectedTextbooks->concat(
                $fallbackActiveTextbooks->groupBy('subject_id')->map(fn($g) => $g->first())->values()
            );
        }

        // Load student's progress for textbook sections
        $allSectionIds = $selectedTextbooks->flatMap(function ($tb) {
            return $tb->chapters->flatMap(function ($c) {
                return $c->sections->pluck('id');
            });
        })->toArray();

        $userProgress = GuidedLearningProgress::where('user_id', $user->id)
            ->whereIn('textbook_section_id', $allSectionIds)
            ->get()
            ->keyBy('textbook_section_id');

        return $selectedTextbooks->map(function (Textbook $tb) use ($userProgress) {
            $sectionIds = $tb->chapters->flatMap(function ($c) {
                return $c->sections->pluck('id');
            })->toArray();

            $total = count($sectionIds);
            $completed = 0;
            $inProgress = 0;

            foreach ($sectionIds as $sId) {
                $status = $userProgress->get($sId)?->status ?? 'not_started';
                if ($status === 'completed') {
                    $completed++;
                } elseif ($status === 'in_progress') {
                    $inProgress++;
                }
            }

            $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
            $sName = strtolower($tb->subject?->name ?? $tb->title);
            $isMath = str_contains($sName, 'math');

            $image = null;
            if ($isMath) {
                $lgSlug = strtolower(
                    $tb->levelGroup?->slug 
                    ?? ($tb->level?->levelGroup?->slug ?? '')
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
                'textbook_id' => $tb->id,
                'encoded_textbook_id' => UrlObfuscator::encode($tb->id),
                'subject_id' => $tb->subject_id,
                'encoded_subject_id' => UrlObfuscator::encode($tb->subject_id),
                'subject_name' => $tb->subject?->name ?? $tb->title,
                'course_title' => $tb->title,
                'tagline' => $tb->author ? "By {$tb->author} • Complete Course Syllabus" : 'Complete course curriculum with interactive lessons and mastery quizzes.',
                'strands_count' => $tb->chapters->count(),
                'total_indicators' => $total,
                'completed_indicators' => $completed,
                'in_progress_indicators' => $inProgress,
                'percentage' => $percentage,
                'icon' => $icon,
                'image' => $image,
                'is_textbook_course' => true,
            ];
        });
    }

    /**
     * Get a complete and published textbook course for a subject if available.
     * Always prioritizes the admin-activated course (is_course_active = true)
     * and gracefully falls back so textbook courses take precedence over raw curriculum.
     */
    public function getPublishedTextbookForSubject(User $user, Subject $subject): ?Textbook
    {
        $selectedLevelGroupSlug = session('selected_level_group') ?? $user->current_level_group;

        $query = Textbook::where('subject_id', $subject->id)
            ->with(['chapters.sections', 'subject', 'level', 'levelGroup']);

        // 1. First priority: Check matching student's current level group or grade
        if ($selectedLevelGroupSlug) {
            $groupQuery = (clone $query)->where(function ($q) use ($selectedLevelGroupSlug) {
                $q->whereHas('levelGroup', function ($lq) use ($selectedLevelGroupSlug) {
                    $lq->where('slug', $selectedLevelGroupSlug)
                       ->orWhere('title', 'like', "%{$selectedLevelGroupSlug}%");
                })->orWhereHas('level.levelGroup', function ($lq) use ($selectedLevelGroupSlug) {
                    $lq->where('slug', $selectedLevelGroupSlug)
                       ->orWhere('title', 'like', "%{$selectedLevelGroupSlug}%");
                });
            });

            $candidates = $groupQuery->orderByDesc('is_course_active')->latest()->get();
            foreach ($candidates as $candidate) {
                if ($candidate->is_course_active && $candidate->chapters->count() > 0) {
                    return $candidate;
                }
                if ($candidate->isCourseComplete()) {
                    return $candidate;
                }
            }
        }

        // 2. Second priority: Fallback to ANY textbook marked active (is_course_active = true) for this subject
        $activeCourse = (clone $query)->where('is_course_active', true)->whereHas('chapters')->latest()->first();
        if ($activeCourse) {
            return $activeCourse;
        }

        // 3. Third priority: Fallback to any complete and published textbook course for this subject
        foreach ($query->latest()->get() as $candidate) {
            if ($candidate->isCourseComplete()) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Format a textbook Table of Contents tree into an interactive learning roadmap for a student.
     */
    public function getTextbookCourseTreeForStudent(User $user, Textbook $textbook): array
    {
        $textbook->load([
            'chapters.sections' => function ($q) {
                $q->orderBy('sort_order');
            },
            'subject',
            'level',
            'levelGroup',
        ]);

        $sectionIds = $textbook->chapters->flatMap(function ($c) {
            return $c->sections->pluck('id');
        })->toArray();

        $userProgress = GuidedLearningProgress::where('user_id', $user->id)
            ->whereIn('textbook_section_id', $sectionIds)
            ->get()
            ->keyBy('textbook_section_id');

        $totalSections = count($sectionIds);
        $completedSections = 0;
        $inProgressSections = 0;

        $strands = [];
        $gradeLabel = $textbook->level?->title ?? $textbook->levelGroup?->title ?? 'Course Modules';

        foreach ($textbook->chapters as $chapter) {
            $chapterSections = [];
            $chapterTotal = 0;
            $chapterCompleted = 0;

            foreach ($chapter->sections as $sec) {
                $cleanSectionTitle = self::cleanTitle($sec->title);
                $prog = $userProgress->get($sec->id);
                $status = $prog?->status ?? 'not_started';

                if ($status === 'completed') {
                    $completedSections++;
                    $chapterCompleted++;
                } elseif ($status === 'in_progress') {
                    $inProgressSections++;
                }
                $chapterTotal++;

                $chapterSections[] = [
                    'id' => $sec->id,
                    'encoded_id' => UrlObfuscator::encode($sec->id),
                    'code' => "Sec " . ($sec->sort_order ?: 1),
                    'title' => $cleanSectionTitle,
                    'status' => $status,
                    'is_completed' => ($status === 'completed'),
                    'completed_at' => $prog?->completed_at?->format('M d, Y'),
                    'quiz_passed_at' => $prog?->quiz_passed_at?->format('M d, Y'),
                    'time_spent' => $prog?->time_spent_seconds ?? 0,
                    'sort_order' => $sec->sort_order,
                    'page_start' => $sec->page_start,
                    'page_end' => $sec->page_end,
                    'is_section' => true,
                    'has_quiz' => !empty($sec->lesson_quiz),
                ];
            }

            $strands[] = [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'grade_label' => $gradeLabel,
                'sort_order' => $chapter->sort_order,
                'total_topics' => $chapterTotal,
                'completed_topics' => $chapterCompleted,
                'sub_strands' => [
                    [
                        'id' => $chapter->id,
                        'title' => $chapter->title,
                        'code' => 'Ch ' . ($chapter->sort_order ?: 1),
                        'sort_order' => $chapter->sort_order,
                        'total_topics' => $chapterTotal,
                        'completed_topics' => $chapterCompleted,
                        'indicators' => $chapterSections,
                    ]
                ],
            ];
        }

        $overallPct = $totalSections > 0 ? round(($completedSections / $totalSections) * 100) : 0;

        $gradeLevels = [
            [
                'label' => $gradeLabel,
                'total_topics' => $totalSections,
                'completed_topics' => $completedSections,
                'progress_percentage' => $overallPct,
                'strands' => $strands,
            ]
        ];

        return [
            'is_textbook_course' => true,
            'textbook' => [
                'id' => $textbook->id,
                'encoded_id' => UrlObfuscator::encode($textbook->id),
                'title' => $textbook->title,
                'author' => $textbook->author,
                'subject_name' => $textbook->subject?->name ?? $textbook->title,
                'subject_id' => $textbook->subject_id,
            ],
            'curriculum' => [
                'id' => $textbook->id,
                'title' => $textbook->title,
                'education_body' => $textbook->publisher ?? 'DigiLearn Academic Course',
                'academic_year' => $textbook->edition ?? date('Y'),
                'subject_name' => $textbook->subject?->name ?? $textbook->title,
                'subject_id' => $textbook->subject_id,
            ],
            'stats' => [
                'total_indicators' => $totalSections,
                'completed_indicators' => $completedSections,
                'in_progress_indicators' => $inProgressSections,
                'overall_percentage' => $overallPct,
            ],
            'grade_label' => $gradeLabel,
            'strands' => $strands,
            'grade_levels' => $gradeLevels,
        ];
    }

    /**
     * Get learning package for a specific textbook section.
     */
    public function getSectionLearningPackage(int $sectionId, User $user): array
    {
        $section = TextbookSection::with([
            'chapter.textbook.subject',
            'media',
            'curriculumIndicator',
        ])->findOrFail($sectionId);

        $chapter = $section->chapter;
        $textbook = $chapter->textbook;
        $subject = $textbook->subject;

        $cleanTitle = self::cleanTitle($section->title);

        $progress = GuidedLearningProgress::where('user_id', $user->id)
            ->where('textbook_section_id', $section->id)
            ->first();

        // Find prev and next sections across the textbook
        $allSections = TextbookSection::whereHas('chapter', function ($q) use ($textbook) {
            $q->where('textbook_id', $textbook->id);
        })->join('textbook_chapters', 'textbook_sections.chapter_id', '=', 'textbook_chapters.id')
          ->orderBy('textbook_chapters.sort_order')
          ->orderBy('textbook_sections.sort_order')
          ->select('textbook_sections.id')
          ->pluck('id')
          ->toArray();

        $currentIndex = array_search($section->id, $allSections);
        $prevId = ($currentIndex !== false && $currentIndex > 0) ? $allSections[$currentIndex - 1] : null;
        $nextId = ($currentIndex !== false && $currentIndex < count($allSections) - 1) ? $allSections[$currentIndex + 1] : null;

        $html = \App\Services\StudentLessonService::cleanMathTypography($section->content_html);
        $markdown = $section->content_markdown;
        if (empty($html) && !empty($markdown)) {
            $html = \App\Services\StudentLessonService::cleanMathTypography(Str::markdown($markdown));
        }

        $quiz = $section->lesson_quiz ?? [];
        if (is_string($quiz)) {
            $quiz = json_decode($quiz, true) ?? [];
        }
        $takeaways = $section->key_takeaways ?? [];
        if (is_string($takeaways)) {
            $takeaways = json_decode($takeaways, true) ?? [];
        }

        $indicatorShim = (object) [
            'id' => $section->id,
            'title' => $cleanTitle,
            'student_title' => $cleanTitle,
            'code' => 'Sec ' . ($section->sort_order ?: 1),
            'indicator_code' => 'Sec ' . ($section->sort_order ?: 1),
            'description' => null,
            'exemplars' => [],
            'page_start' => $section->page_start,
            'page_end' => $section->page_end,
            'lesson_html' => $html,
            'lesson_markdown' => $markdown,
        ];

        $mediaResources = $section->media_resources ?? [];
        if (is_string($mediaResources)) {
            $mediaResources = json_decode($mediaResources, true) ?? [];
        }

        $studentLesson = [
            'html' => $html,
            'markdown' => $markdown,
            'source' => 'textbook',
            'key_takeaways' => is_array($takeaways) ? $takeaways : [],
            'quiz' => is_array($quiz) ? $quiz : [],
            'media_resources' => is_array($mediaResources) ? $mediaResources : [],
            'interactive_segments' => [],
            'has_textbook' => true,
        ];

        $relatedQuizzes = collect();
        if ($section->curriculumIndicator) {
            $relatedQuizzes = $this->findRelevantQuizzes($section->curriculumIndicator, $subject);
        }

        return [
            'isTextbookSection' => true,
            'section' => $section,
            'indicator' => $indicatorShim,
            'encodedIndicatorId' => UrlObfuscator::encode($section->id),
            'subStrand' => (object) [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'code' => 'Ch ' . ($chapter->sort_order ?: 1),
            ],
            'strand' => (object) [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'grade_label' => $textbook->level?->title ?? '',
            ],
            'curriculum' => (object) [
                'id' => $textbook->id,
                'title' => $textbook->title,
            ],
            'subject' => $subject,
            'encodedSubjectId' => UrlObfuscator::encode($subject->id),
            'status' => $progress ? $progress->status : 'not_started',
            'progress' => $progress,
            'textbookSections' => collect([$section]),
            'studentLesson' => $studentLesson,
            'media' => $section->media,
            'curatedMedia' => is_array($mediaResources) ? $mediaResources : [],
            'relatedVideos' => collect(),
            'relatedQuizzes' => $relatedQuizzes,
            'prevIndicatorId' => $prevId ? UrlObfuscator::encode($prevId) : null,
            'nextIndicatorId' => $nextId ? UrlObfuscator::encode($nextId) : null,
        ];
    }

    /**
     * Toggle section completion status.
     */
    public function toggleSectionStatus(User $user, int $sectionId, ?string $explicitStatus = null): GuidedLearningProgress
    {
        $section = TextbookSection::findOrFail($sectionId);

        $progress = GuidedLearningProgress::firstOrNew([
            'user_id' => $user->id,
            'textbook_section_id' => $section->id,
        ]);

        if ($section->curriculum_indicator_id && empty($progress->curriculum_indicator_id)) {
            $progress->curriculum_indicator_id = $section->curriculum_indicator_id;
        }

        if ($explicitStatus) {
            $progress->status = $explicitStatus;
        } else {
            $progress->status = match ($progress->status) {
                'not_started' => 'in_progress',
                'in_progress' => 'completed',
                'completed' => 'not_started',
                default => 'completed',
            };
        }

        if ($progress->status === 'completed') {
            $progress->completed_at = now();
        } else {
            $progress->completed_at = null;
        }

        $progress->save();

        return $progress;
    }

    /**
     * Get the active approved curriculum tree for a student and subject.
     */
    public function getCurriculumTreeForStudent(User $user, Subject $subject): ?array
    {
        // Find approved curriculum matching subject and user's level or level group
        $query = Curriculum::where('subject_id', $subject->id)
            ->where('is_approved', true);

        // Try matching student's current level first
        if (!empty($user->current_level)) {
            $matchedLevel = \App\Models\Level::where('title', 'like', '%' . $user->current_level . '%')
                ->orWhere('slug', 'like', '%' . $user->current_level . '%')
                ->first();

            if ($matchedLevel) {
                $levelCurriculum = (clone $query)->where('level_id', $matchedLevel->id)->first();
                if ($levelCurriculum) {
                    return $this->formatCurriculumTree($levelCurriculum, $user);
                }
            }
        }

        // Try matching level group (e.g. jhs, shs, primary-upper)
        $levelGroup = session('selected_level_group') ?? $user->current_level_group;
        if ($levelGroup) {
            $groupCurriculum = (clone $query)->whereHas('levelGroup', function ($q) use ($levelGroup) {
                $q->where('slug', $levelGroup)->orWhere('title', 'like', "%{$levelGroup}%");
            })->first();

            if ($groupCurriculum) {
                return $this->formatCurriculumTree($groupCurriculum, $user);
            }
        }

        // Fallback to the latest approved curriculum for this subject
        $anyCurriculum = $query->latest()->first();
        if ($anyCurriculum) {
            return $this->formatCurriculumTree($anyCurriculum, $user);
        }

        return null;
    }

    /**
     * Clean any indicator/strand numbering prefix (e.g. "Strand 1:", "B7.1.1.1.1:", "Indicator 1:").
     */
    public static function cleanTitle(?string $title): string
    {
        if (!$title) return '';
        $cleaned = preg_replace('/^(?:(?:Strand|Sub-Strand|Indicator|Content\s*Standard)\s*\d*(?:\.\d+)*\s*[:\-]?\s*|B\d+(?:\.\d+)+\s*[:\-]?\s*)/i', '', $title);
        return trim($cleaned);
    }

    /**
     * Format curriculum into an interactive tree with student progress.
     */
    public function formatCurriculumTree(Curriculum $curriculum, User $user): array
    {
        $curriculum->load([
            'strands.subStrands.indicators' => function ($q) {
                $q->orderBy('sort_order');
            }
        ]);

        $userProgress = GuidedLearningProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('curriculum_indicator_id');

        $totalIndicators = 0;
        $completedIndicators = 0;
        $inProgressIndicators = 0;

        $strands = [];
        foreach ($curriculum->strands as $strand) {
            $subStrands = [];
            $strandTotal = 0;
            $strandCompleted = 0;

            foreach ($strand->subStrands as $subStrand) {
                $indicators = [];
                foreach ($subStrand->indicators as $ind) {
                    $totalIndicators++;
                    $strandTotal++;

                    $prog = $userProgress->get($ind->id);
                    $status = $prog ? $prog->status : 'not_started';

                    if ($status === 'completed') {
                        $completedIndicators++;
                        $strandCompleted++;
                    } elseif ($status === 'in_progress') {
                        $inProgressIndicators++;
                    }

                    $cleanIndTitle = $ind->student_title;

                    $indicators[] = [
                        'id' => $ind->id,
                        'encoded_id' => UrlObfuscator::encode($ind->id),
                        'code' => $ind->indicator_code,
                        'title' => $cleanIndTitle,
                        'description' => $ind->description,
                        'exemplars' => $ind->exemplars,
                        'status' => $status,
                        'completed_at' => $prog?->completed_at?->format('M d, Y'),
                        'time_spent_seconds' => $prog?->time_spent_seconds ?? 0,
                    ];
                }

                $subStrands[] = [
                    'id' => $subStrand->id,
                    'title' => self::cleanTitle($subStrand->title),
                    'description' => $subStrand->description,
                    'content_standard' => $subStrand->content_standard,
                    'indicators' => $indicators,
                ];
            }

            $strandProgressPct = $strandTotal > 0 ? round(($strandCompleted / $strandTotal) * 100) : 0;

            $strands[] = [
                'id' => $strand->id,
                'title' => self::cleanTitle($strand->title),
                'description' => $strand->description,
                'grade_label' => $strand->grade_label,
                'total_topics' => $strandTotal,
                'completed_topics' => $strandCompleted,
                'progress_percentage' => $strandProgressPct,
                'sub_strands' => $subStrands,
            ];
        }

        // Group strands into distinct Grade/Level sections
        $gradeLevels = [];
        foreach ($strands as $st) {
            $label = !empty($st['grade_label']) ? $st['grade_label'] : 'Standard Curriculum';
            if (!isset($gradeLevels[$label])) {
                $gradeLevels[$label] = [
                    'label' => $label,
                    'total_topics' => 0,
                    'completed_topics' => 0,
                    'strands' => [],
                ];
            }
            $gradeLevels[$label]['total_topics'] += $st['total_topics'];
            $gradeLevels[$label]['completed_topics'] += $st['completed_topics'];
            $gradeLevels[$label]['strands'][] = $st;
        }

        foreach ($gradeLevels as &$gl) {
            $gl['progress_percentage'] = $gl['total_topics'] > 0
                ? round(($gl['completed_topics'] / $gl['total_topics']) * 100)
                : 0;
        }
        unset($gl);
        $gradeLevels = array_values($gradeLevels);

        $overallPct = $totalIndicators > 0 ? round(($completedIndicators / $totalIndicators) * 100) : 0;

        return [
            'curriculum' => [
                'id' => $curriculum->id,
                'title' => $curriculum->title,
                'education_body' => $curriculum->education_body,
                'academic_year' => $curriculum->academic_year,
                'subject_name' => $curriculum->subject->name,
                'subject_id' => $curriculum->subject_id,
            ],
            'stats' => [
                'total_indicators' => $totalIndicators,
                'completed_indicators' => $completedIndicators,
                'in_progress_indicators' => $inProgressIndicators,
                'overall_percentage' => $overallPct,
            ],
            'strands' => $strands,
            'grade_levels' => $gradeLevels,
        ];
    }

    /**
     * Toggle indicator completion status.
     */
    public function toggleIndicatorStatus(User $user, int $indicatorId, ?string $explicitStatus = null): GuidedLearningProgress
    {
        $indicator = CurriculumIndicator::findOrFail($indicatorId);

        $progress = GuidedLearningProgress::firstOrNew([
            'user_id' => $user->id,
            'curriculum_indicator_id' => $indicator->id,
        ]);

        if ($explicitStatus) {
            $progress->status = $explicitStatus;
        } else {
            // Cycle status: not_started -> in_progress -> completed -> not_started
            $progress->status = match ($progress->status) {
                'not_started' => 'in_progress',
                'in_progress' => 'completed',
                'completed' => 'not_started',
                default => 'completed',
            };
        }

        if ($progress->status === 'completed') {
            $progress->completed_at = now();
        } else {
            $progress->completed_at = null;
        }

        $progress->save();

        return $progress;
    }

    /**
     * Get a specific indicator with all attached learning resources.
     */
    public function getIndicatorLearningPackage(int $indicatorId, User $user): array
    {
        $indicator = CurriculumIndicator::with([
            'subStrand.strand.curriculum.subject',
            'textbookSections.media',
            'textbookSections.chapter.textbook',
            'media',
        ])->findOrFail($indicatorId);

        $indicator->title = self::cleanTitle($indicator->title);
        if ($indicator->subStrand) {
            $indicator->subStrand->title = self::cleanTitle($indicator->subStrand->title);
            if ($indicator->subStrand->strand) {
                $indicator->subStrand->strand->title = self::cleanTitle($indicator->subStrand->strand->title);
            }
        }

        $curriculum = $indicator->subStrand->strand->curriculum;
        $subject = $curriculum->subject;

        $progress = GuidedLearningProgress::where('user_id', $user->id)
            ->where('curriculum_indicator_id', $indicator->id)
            ->first();

        // Find related videos using keyword-scored relevance matching
        $relatedVideos = $this->findRelevantVideos($indicator, $subject);

        // Find related quizzes using keyword-scored relevance matching
        $relatedQuizzes = $this->findRelevantQuizzes($indicator, $subject);

        // Find next and previous indicators
        $allIndicators = CurriculumIndicator::whereHas('subStrand.strand', function ($q) use ($curriculum) {
            $q->where('curriculum_id', $curriculum->id);
        })->orderBy('id')->pluck('id')->toArray();

        $currentIndex = array_search($indicator->id, $allIndicators);
        $prevId = ($currentIndex !== false && $currentIndex > 0) ? $allIndicators[$currentIndex - 1] : null;
        $nextId = ($currentIndex !== false && $currentIndex < count($allIndicators) - 1) ? $allIndicators[$currentIndex + 1] : null;

        // Resolve student lesson notes (Tier 1: Textbook -> Tier 2: Cached AI -> Tier 3: Local Synthesizer)
        $studentLesson = app(StudentLessonService::class)->getLesson($indicator);

        return [
            'indicator' => $indicator,
            'encodedIndicatorId' => UrlObfuscator::encode($indicator->id),
            'subStrand' => $indicator->subStrand,
            'strand' => $indicator->subStrand->strand,
            'curriculum' => $curriculum,
            'subject' => $subject,
            'encodedSubjectId' => UrlObfuscator::encode($subject->id),
            'status' => $progress ? $progress->status : 'not_started',
            'progress' => $progress,
            'textbookSections' => $indicator->textbookSections,
            'studentLesson' => $studentLesson,
            'media' => $indicator->media,
            'relatedVideos' => $relatedVideos,
            'relatedQuizzes' => $relatedQuizzes,
            'prevIndicatorId' => $prevId ? UrlObfuscator::encode($prevId) : null,
            'nextIndicatorId' => $nextId ? UrlObfuscator::encode($nextId) : null,
        ];
    }

    /**
     * Find genuinely relevant videos for an indicator using keyword-scored matching.
     * Falls back to YouTube search when DB has no relevant matches, caching results for future visits.
     */
    private function findRelevantVideos(CurriculumIndicator $indicator, Subject $subject): \Illuminate\Support\Collection
    {
        $primaryKeywords = $this->extractPrimaryTopicKeywords($indicator);
        $keywords = $this->extractTopicKeywords($indicator);
        $gradeLabel = $indicator->subStrand?->strand?->grade_label;

        // 1. Score existing DB videos by keyword relevance (must match specific topic)
        $dbResults = $this->scoreDbVideos($indicator, $keywords, $primaryKeywords, $subject, $gradeLabel);

        if ($dbResults->isNotEmpty()) {
            return $dbResults;
        }

        // 2. No DB match — search YouTube, save results to DB, return them
        return $this->searchAndCacheYouTubeVideos($indicator, $subject, $keywords, $gradeLabel);
    }

    /**
     * Score all DB videos against topic keywords, return only genuinely relevant ones.
     */
    private function scoreDbVideos(CurriculumIndicator $indicator, array $keywords, array $primaryKeywords, Subject $subject, ?string $gradeLabel): \Illuminate\Support\Collection
    {
        // STRICT: Only look for videos belonging to this exact subject!
        $candidates = Video::where('subject_id', $subject->id)->get();

        $strandTitle = strtoupper($indicator->subStrand?->strand?->title ?? '');
        $isAlgebra = str_contains($strandTitle, 'ALGEBRA');
        $isGeometry = str_contains($strandTitle, 'GEOMETRY');
        $isData = str_contains($strandTitle, 'DATA') || str_contains($strandTitle, 'STATISTICS');
        $isNumber = str_contains($strandTitle, 'NUMBER') || str_contains($strandTitle, 'NUMERACY');

        $scored = [];
        foreach ($candidates as $video) {
            $videoText = $video->title . ' ' . ($video->description ?? '') . ' ' . ($video->unit_name ?? '') . ' ' . ($video->agent_topic ?? '');

            // Cross-strand anti-domain filter: NEVER recommend videos from completely conflicting math branches!
            if ($isAlgebra && preg_match('/\b(angle|angles|parallel lines|transversal|triangle|triangles|bearing|bearings|rounding decimals|significant figures|place value|surds|standard form|geometry)\b/i', $videoText)) {
                continue;
            }
            if ($isGeometry && preg_match('/\b(algebra|factoris|factoriz|linear equation|inequalit|surd|surds|rounding decimals|place value|venn|probability|stem and leaf)\b/i', $videoText)) {
                continue;
            }
            if ($isData && preg_match('/\b(geometry|angle|angles|bearing|bearings|pythagor|trigonometr)\b/i', $videoText)) {
                continue;
            }

            // STRICT: If we have primary topic keywords, the video MUST match at least one!
            if (!empty($primaryKeywords)) {
                $primaryScore = $this->scoreContentRelevance($videoText, $primaryKeywords);
                if ($primaryScore < 1) {
                    continue;
                }
            }

            $keywordScore = $this->scoreContentRelevance($videoText, $keywords);

            if ($keywordScore < 2) {
                continue;
            }

            $score = $keywordScore;

            // Bonus for matching grade level
            if ($gradeLabel && $video->grade_level && stripos($video->grade_level, $gradeLabel) !== false) {
                $score += 2;
            }

            $scored[] = ['video' => $video, 'score' => $score];
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        $topResults = array_slice($scored, 0, 4);

        return collect(array_map(fn($item) => $item['video'], $topResults));
    }

    /**
     * Search YouTube for topic-relevant videos, save the best matches to the DB,
     * and return them. Uses a cache lock to avoid duplicate API calls.
     * Guaranteed not to hang — 10s timeout on the HTTP call.
     */
    private function searchAndCacheYouTubeVideos(
        CurriculumIndicator $indicator,
        Subject $subject,
        array $keywords,
        ?string $gradeLabel
    ): \Illuminate\Support\Collection {
        $cacheKey = 'yt_search_indicator_' . $indicator->id;

        // Don't re-search if we already searched recently (24h cooldown)
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return collect();
        }

        try {
            $youtubeApiKey = config('services.youtube.api_key');
            if (empty($youtubeApiKey)) {
                return collect();
            }

            // Build a high-precision educational query: topic + substrand + subject + grade
            $cleanGrade = $gradeLabel ? preg_replace('/[()]/', '', $gradeLabel) : 'Basic 7';
            $subStrandName = trim(preg_replace('/^SUB-STRAND\s*\d*[:\-]?\s*/i', '', $indicator->subStrand?->title ?? ''));

            $searchTerms = [];
            $searchTerms[] = $indicator->student_title;
            if (!empty($subStrandName) && stripos($indicator->student_title, $subStrandName) === false) {
                $searchTerms[] = $subStrandName;
            }
            $searchTerms[] = $subject->name;
            $searchTerms[] = $cleanGrade;

            $searchQuery = implode(' ', array_filter($searchTerms));

            // Search YouTube with a short timeout (never hang)
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->get('https://www.googleapis.com/youtube/v3/search', [
                    'key' => $youtubeApiKey,
                    'q' => $searchQuery,
                    'part' => 'snippet',
                    'type' => 'video',
                    'maxResults' => 6,
                    'relevanceLanguage' => 'en',
                    'videoDuration' => 'medium', // 4-20 minutes (educational sweet spot)
                    'safeSearch' => 'strict',
                ]);

            if (!$response->successful()) {
                \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHours(1));
                return collect();
            }

            $items = $response->json('items', []);
            if (empty($items)) {
                \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHours(6));
                return collect();
            }

            // Score YouTube results against our keywords, pick the best
            $ytScored = [];
            foreach ($items as $item) {
                $videoId = $item['id']['videoId'] ?? null;
                if (!$videoId) continue;

                // Skip if already in our DB
                if (Video::where('external_video_id', $videoId)->exists()) continue;

                $title = html_entity_decode($item['snippet']['title'] ?? 'Untitled', ENT_QUOTES, 'UTF-8');
                $description = html_entity_decode($item['snippet']['description'] ?? '', ENT_QUOTES, 'UTF-8');
                $channelTitle = $item['snippet']['channelTitle'] ?? '';

                $score = $this->scoreContentRelevance($title . ' ' . $description, $keywords);

                // Boost educational channels
                $eduChannels = ['khan academy', 'organic chemistry tutor', 'math antics', 'fuse school', 'professor leonard', 'eddie woo', 'hub beyond numbers', 'easy mathematics'];
                foreach ($eduChannels as $edu) {
                    if (stripos($channelTitle, $edu) !== false) {
                        $score += 3;
                        break;
                    }
                }

                if ($score >= 1) {
                    $ytScored[] = [
                        'videoId' => $videoId,
                        'title' => $title,
                        'description' => $description,
                        'channelTitle' => $channelTitle,
                        'thumbnail' => $item['snippet']['thumbnails']['high']['url']
                            ?? $item['snippet']['thumbnails']['default']['url']
                            ?? null,
                        'score' => $score,
                    ];
                }
            }

            usort($ytScored, fn($a, $b) => $b['score'] <=> $a['score']);
            $bestResults = array_slice($ytScored, 0, 3);

            $adminUserId = Auth::id()
                ?? User::where('is_admin', true)->value('id')
                ?? User::min('id')
                ?? 1;

            // Save the best YouTube results to the DB (cached for all future visits)
            $savedVideos = [];
            foreach ($bestResults as $yt) {
                $duration = null;
                try {
                    $duration = app(YouTubeService::class)->getVideoDuration($yt['videoId']);
                } catch (\Exception $e) {
                    $duration = null;
                }

                $video = Video::create([
                    'title' => $yt['title'],
                    'description' => mb_substr($yt['description'], 0, 500),
                    'subject_id' => $subject->id,
                    'grade_level' => $gradeLabel ?? 'JHS 1',
                    'video_source' => 'youtube',
                    'external_video_id' => $yt['videoId'],
                    'external_video_url' => 'https://www.youtube.com/embed/' . $yt['videoId'],
                    'thumbnail_path' => null, // Will use YouTube thumbnail via getThumbnailUrl()
                    'duration_seconds' => $duration,
                    'uploaded_by' => $adminUserId,
                    'status' => 'approved',
                    'is_agent_generated' => true,
                    'agent_topic' => $indicator->student_title,
                ]);
                $savedVideos[] = $video;
            }

            // Mark this indicator as searched (24h cooldown)
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHours(24));

            return collect($savedVideos);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('YouTube search for guided learning failed', [
                'indicator_id' => $indicator->id,
                'error' => $e->getMessage(),
            ]);
            // Set a short cooldown so we don't spam on errors
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addMinutes(30));
            return collect();
        }
    }

    /**
     * Find genuinely relevant quizzes for an indicator using keyword-scored matching.
     */
    private function findRelevantQuizzes(CurriculumIndicator $indicator, Subject $subject): \Illuminate\Support\Collection
    {
        $keywords = $this->extractTopicKeywords($indicator);

        $candidates = Quiz::where('subject_id', $subject->id)->get();

        $scored = [];
        foreach ($candidates as $quiz) {
            $score = $this->scoreContentRelevance($quiz->title . ' ' . ($quiz->description ?? ''), $keywords);

            if ($score >= 2) {
                $scored[] = ['quiz' => $quiz, 'score' => $score];
            }
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        $topResults = array_slice($scored, 0, 3);

        return collect(array_map(fn($item) => $item['quiz'], $topResults));
    }

    /**
     * Extract specific keywords strictly from the indicator title and student title.
     * These identify the exact topic concept (e.g. "angles", "alternate", "triangle").
     *
     * @return array<string>
     */
    private function getTopicStopwords(): array
    {
        return [
            'the', 'a', 'an', 'of', 'in', 'to', 'for', 'and', 'or', 'by', 'on', 'at',
            'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had',
            'do', 'does', 'did', 'will', 'shall', 'would', 'could', 'should', 'may',
            'can', 'with', 'from', 'that', 'this', 'these', 'those', 'it', 'its',
            'as', 'not', 'but', 'if', 'than', 'more', 'less', 'using', 'use',
            'model', 'demonstrate', 'understanding', 'apply', 'perform', 'identify',
            'describe', 'explain', 'show', 'represent', 'determine', 'solve',
            'learners', 'students', 'teacher', 'up', 'down', 'off', 'out', 'their',
            'according', 'measured', 'sizes', 'values', 'draw', 'working', 'practicing',
            'recognizing', 'concept', 'calculate', 'calculating', 'write', 'writing',
            'read', 'reading', 'form', 'forms', 'line', 'lines', 'simple', 'given',
            'review', 'express', 'expressing', 'plot', 'plotting', 'construct',
            'constructing', 'find', 'finding', 'table', 'tables', 'part', 'parts',
            'knowledge', 'real', 'life', 'problems', 'problem', 'state', 'stating',
            'measure', 'measures',
            'how', 'one', 'two', 'three', 'each', 'every', 'all', 'any', 'without', 'within',
            'between', 'among', 'across', 'through', 'into', 'onto', 'upon', 'about', 'above',
            'below', 'around', 'presented', 'presentation', 'material', 'materials', 'symbolic',
            'concrete', 'abstract', 'preceding', 'succeeding', 'following', 'differ', 'differs',
            'differing', 'different', 'element', 'elements', 'item', 'items', 'thing', 'things',
            'way', 'ways', 'step', 'steps', 'type', 'types', 'kind', 'kinds', 'such', 'etc',
            'also', 'just', 'well', 'like', 'same', 'both', 'another', 'many', 'much', 'some',
        ];
    }

    private function extractPrimaryTopicKeywords(CurriculumIndicator $indicator): array
    {
        $sources = [
            $indicator->student_title,
            $indicator->title,
        ];

        $text = strtolower(implode(' ', array_filter($sources)));
        $text = preg_replace('/b\d+(\.\d+)+/i', '', $text);
        $text = preg_replace('/\b(strand|sub-strand|indicator|content\s*standard)\s*\d*\s*[:\-]?\s*/i', '', $text);
        $words = preg_split('/[\s,;:\.\(\)\[\]\/\-]+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        $stopwords = $this->getTopicStopwords();

        $keywords = [];
        foreach ($words as $word) {
            if (strlen($word) >= 3 && !in_array($word, $stopwords) && !is_numeric($word)) {
                $keywords[$word] = true;
            }
        }

        $keywords = array_keys($keywords);
        usort($keywords, fn($a, $b) => strlen($b) <=> strlen($a));

        return $keywords;
    }

    /**
     * Extract meaningful topic keywords from an indicator and its curriculum hierarchy.
     * Filters out common stopwords and short fragments to produce search-quality terms.
     *
     * @return array<string> Lowercase keywords sorted by length (longest first)
     */
    private function extractTopicKeywords(CurriculumIndicator $indicator): array
    {
        // Gather text from all relevant context levels
        $sources = [
            $indicator->title,
            $indicator->description,
            $indicator->student_title,
            $indicator->subStrand?->title,
            $indicator->subStrand?->strand?->title,
        ];

        $text = strtolower(implode(' ', array_filter($sources)));

        // Remove curriculum codes (e.g. B7.1.1.1.1)
        $text = preg_replace('/b\d+(\.\d+)+/i', '', $text);

        // Remove common curriculum prefixes
        $text = preg_replace('/\b(strand|sub-strand|indicator|content\s*standard)\s*\d*\s*[:\-]?\s*/i', '', $text);

        // Split into words
        $words = preg_split('/[\s,;:\.\(\)\[\]\/\-]+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        $stopwords = $this->getTopicStopwords();

        $keywords = [];
        foreach ($words as $word) {
            if (strlen($word) >= 3 && !in_array($word, $stopwords) && !is_numeric($word)) {
                $keywords[$word] = true; // deduplicate
            }
        }

        $keywords = array_keys($keywords);

        // Sort by length descending so longer (more specific) terms match first
        usort($keywords, fn($a, $b) => strlen($b) <=> strlen($a));

        return $keywords;
    }

    /**
     * Score how relevant a text blob is to a set of topic keywords.
     * Uses word boundary matching to prevent substring false positives.
     * Returns an integer score (0 = no relevance).
     */
    private function scoreContentRelevance(string $text, array $keywords): int
    {
        $text = strtolower($text);
        $score = 0;

        foreach ($keywords as $keyword) {
            $kw = strtolower($keyword);
            if (strlen($kw) < 3) continue;

            if (preg_match('/\b' . preg_quote($kw, '/') . '(s|es|ed|ing)?\b/i', $text)) {
                // Longer, more specific keywords carry higher weight
                $score += strlen($kw) >= 6 ? 3 : 2;
            }
        }

        return $score;
    }
}
