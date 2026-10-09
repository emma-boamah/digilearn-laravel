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
