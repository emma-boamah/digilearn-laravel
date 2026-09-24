<?php

namespace App\Services;

use App\Models\Curriculum;
use App\Models\CurriculumIndicator;
use App\Models\GuidedLearningProgress;
use App\Models\Subject;
use App\Models\User;
use App\Models\Video;
use App\Models\Document;
use App\Models\Quiz;
use App\Services\UrlObfuscator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GuidedLearningService
{
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

                    $cleanIndTitle = self::cleanTitle($ind->title);

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

        // Find related videos by subject and topic matching
        $relatedVideos = Video::where('subject_id', $subject->id)
            ->where(function ($q) use ($indicator) {
                $q->where('title', 'like', '%' . mb_substr($indicator->title, 0, 20) . '%')
                  ->orWhere('description', 'like', '%' . mb_substr($indicator->title, 0, 20) . '%')
                  ->orWhere('unit_name', 'like', '%' . mb_substr($indicator->subStrand->title, 0, 20) . '%');
            })
            ->take(4)
            ->get();

        if ($relatedVideos->isEmpty()) {
            // Fallback to top videos for the subject
            $relatedVideos = Video::where('subject_id', $subject->id)->take(3)->get();
        }

        // Find related quizzes
        $relatedQuizzes = Quiz::where('subject_id', $subject->id)
            ->where(function ($q) use ($indicator) {
                $q->where('title', 'like', '%' . mb_substr($indicator->title, 0, 20) . '%');
            })
            ->take(3)
            ->get();

        if ($relatedQuizzes->isEmpty()) {
            $relatedQuizzes = Quiz::where('subject_id', $subject->id)->take(2)->get();
        }

        // Find next and previous indicators
        $allIndicators = CurriculumIndicator::whereHas('subStrand.strand', function ($q) use ($curriculum) {
            $q->where('curriculum_id', $curriculum->id);
        })->orderBy('id')->pluck('id')->toArray();

        $currentIndex = array_search($indicator->id, $allIndicators);
        $prevId = ($currentIndex !== false && $currentIndex > 0) ? $allIndicators[$currentIndex - 1] : null;
        $nextId = ($currentIndex !== false && $currentIndex < count($allIndicators) - 1) ? $allIndicators[$currentIndex + 1] : null;

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
            'media' => $indicator->media,
            'relatedVideos' => $relatedVideos,
            'relatedQuizzes' => $relatedQuizzes,
            'prevIndicatorId' => $prevId ? UrlObfuscator::encode($prevId) : null,
            'nextIndicatorId' => $nextId ? UrlObfuscator::encode($nextId) : null,
        ];
    }
}
