<?php

namespace App\Services;

use App\Models\AgentRequest;
use App\Models\Subject;
use App\Models\User;
use App\Models\Video;
use App\Models\SearchAnalytic;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class LearningAgentService
{
    private string $geminiApiKey;
    private string $geminiModel;
    private YouTubeService $youtubeService;

    /** Maximum agent requests per user per day */
    private const DAILY_LIMIT = 10;

    /** Minimum video duration (seconds) — skip very short clips */
    private const MIN_DURATION_SECONDS = 120;

    /** Maximum video duration (seconds) — skip very long lectures */
    private const MAX_DURATION_SECONDS = 1800;

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.key', '');
        $this->geminiModel = config('services.gemini.model', 'gemini-1.5-flash');
        $this->youtubeService = app(YouTubeService::class);
    }

    /**
     * Detect the user's intent from their query to route to the correct feature.
     */
    public function detectIntent(string $query, string $defaultType): string
    {
        $queryLower = strtolower($query);

        // 1. Check for Quiz intent (e.g. "create a quiz", "test me", "assessment on X")
        if (preg_match('/\b(quiz|test|exam|assessment|questions|answer|test me|test my knowledge|give me a quiz|create a quiz|generate a quiz|mock exam)\b/i', $queryLower)) {
            return 'quiz';
        }

        // 2. Check for Roadmap intent (e.g. "roadmap for X", "how to learn X", "learning path")
        if (preg_match('/\b(roadmap|curriculum|syllabus|plan|series|how to learn|step by step|guide|learning path|study plan|course path)\b/i', $queryLower)) {
            return 'roadmap';
        }

        // 3. Check for Lesson intent (explicit video requests or standard learning)
        if (preg_match('/\b(video|watch|show me|lesson on|explain|tutorial|teach me|tell me about)\b/i', $queryLower)) {
            return 'lesson';
        }

        // Default to the type explicitly selected in the UI if no strong keywords found
        return $defaultType;
    }

    /**
     * Main entry point: find or create a lesson for the user's query.
     *
     * @return array{success: bool, video_id: ?int, lesson_url: ?string, topic: ?string, message: string, is_existing: bool}
     */
    public function findOrCreateLesson(string $query, User $user): array
    {
        $startTime = microtime(true);

        // Get user's grade context
        $gradeLevel = $user->grade ?? 'Primary 1';
        $levelGroup = $user->current_level_group ?? 'primary-lower';

        // Capture metadata
        $ip = request()->ip();
        $ua = request()->userAgent();

        // Create tracking record
        $agentRequest = AgentRequest::create([
            'user_id' => $user->id,
            'query' => $query,
            'grade_level' => $gradeLevel,
            'level_group' => $levelGroup,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'status' => 'pending',
        ]);

        // Log to search analytics for trending analysis
        $this->logToSearchAnalytics($query, $user->id);

        try {
            // Rate limit check
            if ($this->isRateLimited($user->id)) {
                $agentRequest->update([
                    'status' => 'failed',
                    'error_message' => 'Daily request limit reached',
                ]);
                return [
                    'success' => false,
                    'video_id' => null,
                    'lesson_url' => null,
                    'topic' => null,
                    'message' => 'You\'ve reached your daily limit of ' . self::DAILY_LIMIT . ' AI lesson requests. Try again tomorrow!',
                    'is_existing' => false,
                ];
            }

            // Step 1: Analyze query with Gemini
            $agentRequest->update(['status' => 'analyzing']);
            $analysis = $this->analyzeQuery($query, $gradeLevel, $levelGroup);

            $agentRequest->update([
                'topic' => $analysis['topic'] ?? null,
                'subject' => $analysis['subject'] ?? null,
                'gemini_response' => $analysis,
            ]);



            if (isset($analysis['is_supported']) && $analysis['is_supported'] === false) {
                $agentRequest->update([
                    'status' => 'failed',
                    'error_message' => 'Unsupported request type: ' . ($analysis['refusal_message'] ?? 'Out of scope'),
                ]);
                return [
                    'success' => false,
                    'message' => $analysis['refusal_message'] ?? "I'm sorry, I'm specialized in finding educational videos and roadmaps. I can't do that specific task, but I'd be happy to find you a lesson on it!",
                    'is_supported' => false,
                ];
            }

            if (empty($analysis['topic'])) {
                $agentRequest->update([
                    'status' => 'failed',
                    'error_message' => 'Could not understand the topic from your query',
                ]);
                return [
                    'success' => false,
                    'video_id' => null,
                    'lesson_url' => null,
                    'topic' => null,
                    'message' => 'I couldn\'t understand what topic you\'re looking for. Try being more specific, like "Teach me about photosynthesis" or "Explain quadratic equations".',
                    'is_existing' => false,
                ];
            }

            // Step 2: Check for existing content
            $existingVideo = $this->findExistingContent(
                $analysis['topic'],
                $analysis['search_keywords'] ?? [],
                $gradeLevel,
                $analysis['subject'] ?? null
            );

            if ($existingVideo) {
                $processingTime = (int)((microtime(true) - $startTime) * 1000);
                $summary = $analysis['summary'] ?? ($this->getTopicSummary($analysis['topic'], $gradeLevel, $levelGroup) ?? $existingVideo->description);
                
                $agentRequest->update([
                    'status' => 'found_existing',
                    'video_id' => $existingVideo->id,
                    'processing_time_ms' => $processingTime,
                    'gemini_response' => array_merge($analysis, ['summary' => $summary])
                ]);

                return [
                    'success' => true,
                    'video_id' => $existingVideo->id,
                    'lesson_url' => $this->buildLessonUrl($existingVideo),
                    'topic' => $analysis['topic'],
                    'message' => 'Great news! We already have a lesson on "' . $analysis['topic'] . '" for your level.',
                    'is_existing' => true,
                    'thumbnail' => $existingVideo->getThumbnailUrl(),
                    'title' => $existingVideo->title,
                    'duration' => $existingVideo->duration_seconds,
                    'summary' => $summary,
                ];
            }

            // Step 3: Search YouTube
            $agentRequest->update(['status' => 'searching']);
            $searchQuery = $analysis['youtube_search_query'] ?? $analysis['topic'] . ' explained for ' . $gradeLevel . ' students';
            $youtubeResults = $this->searchYouTube($searchQuery, $gradeLevel);

            $agentRequest->update(['youtube_results' => $youtubeResults]);

            if (empty($youtubeResults)) {
                $agentRequest->update([
                    'status' => 'failed',
                    'error_message' => 'No suitable YouTube videos found',
                ]);
                return [
                    'success' => false,
                    'video_id' => null,
                    'lesson_url' => null,
                    'topic' => $analysis['topic'],
                    'message' => 'I couldn\'t find a suitable video lesson on "' . $analysis['topic'] . '" right now. Try rephrasing your request or check back later!',
                    'is_existing' => false,
                ];
            }

            // Step 4: Pick the best video (optionally use Gemini to rank)
            $bestVideo = $this->pickBestVideo($youtubeResults, $analysis);

            if (!$bestVideo) {
                $agentRequest->update([
                    'status' => 'failed',
                    'error_message' => 'No video passed quality filter',
                ]);
                return [
                    'success' => false,
                    'video_id' => null,
                    'lesson_url' => null,
                    'topic' => $analysis['topic'],
                    'message' => 'I found some videos but none met our quality standards for your level. Try a different topic!',
                    'is_existing' => false,
                ];
            }

            // Step 5: Create the Video record
            $video = $this->createVideoRecord($bestVideo, $analysis, $user, $query);

            $processingTime = (int)((microtime(true) - $startTime) * 1000);
            $summary = $analysis['summary'] ?? ($this->getTopicSummary($analysis['topic'], $gradeLevel, $levelGroup) ?? $video->description);
            $agentRequest->update([
                'status' => 'created',
                'video_id' => $video->id,
                'youtube_video_id' => $bestVideo['videoId'],
                'processing_time_ms' => $processingTime,
                'gemini_response' => array_merge($analysis, ['summary' => $summary])
            ]);

            return [
                'success' => true,
                'video_id' => $video->id,
                'lesson_url' => $this->buildLessonUrl($video),
                'topic' => $analysis['topic'],
                'message' => 'I found a great lesson on "' . $analysis['topic'] . '" for you!',
                'is_existing' => false,
                'thumbnail' => $video->getThumbnailUrl(),
                'title' => $video->title,
                'duration' => $video->duration_seconds,
                'summary' => $summary,
            ];

        } catch (Exception $e) {
            $processingTime = (int)((microtime(true) - $startTime) * 1000);

            Log::error('LearningAgentService error', [
                'user_id' => $user->id,
                'query' => $query,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $agentRequest->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'processing_time_ms' => $processingTime,
            ]);

            return [
                'success' => false,
                'video_id' => null,
                'lesson_url' => null,
                'topic' => null,
                'message' => 'Something went wrong while searching for your lesson. Please try again!',
                'is_existing' => false,
            ];
        }
    }

    /**
     * Check if user has exceeded daily rate limit.
     */
    private function isRateLimited(int $userId): bool
    {
        return AgentRequest::todayCountForUser($userId) >= self::DAILY_LIMIT;
    }

    /**
     * Step 1: Use Gemini to analyze the user's natural language query.
     */
    private function analyzeQuery(string $query, string $gradeLevel, string $levelGroup): array
    {
        if (empty($this->geminiApiKey)) {
            // Fallback: simple extraction without AI
            return $this->fallbackAnalysis($query, $gradeLevel);
        }

        $gradeLevelContext = $this->getGradeLevelContext($gradeLevel, $levelGroup);

        $prompt = <<<PROMPT
You are an educational content curator for a Ghanaian e-learning platform. A student has asked for a lesson.

Student's query: "{$query}"
Student's grade level: {$gradeLevel}
Educational context: {$gradeLevelContext}

Analyze this request and return a JSON object with:
1. "topic" - The core educational topic (concise, 2-5 words). e.g. "Photosynthesis", "Quadratic Equations", "Water Cycle"
2. "subject" - The academic subject this belongs to. Must be one of: Mathematics, English, Science, Social Studies, ICT, French, Religious and Moral Education, Creative Arts, Ghanaian Language, History, Geography, Physics, Chemistry, Biology, Economics, Government, Literature, Integrated Science, Elective Mathematics, General Science. Pick the closest match.
3. "search_keywords" - Array of 3-5 keywords for finding educational videos on this topic
4. "youtube_search_query" - A single optimized YouTube search string that would find the best educational video for this student's level. Include grade-appropriate language markers (e.g. "for junior high school", "simple explanation for kids", "GCSE level")
5. "summary" - A comprehensive 2-3 paragraph educational summary of the topic ITSELF (e.g. if they ask about "respiration", explain what respiration is, the key concepts, and why it's important). This MUST be an educational explanation of the topic, tailored to their grade level.
6. "ges_relevance" - A brief sentence on how this aligns with the GES syllabus.
7. "difficulty_level" - One of: "beginner", "intermediate", "advanced"
8. "is_valid_educational_topic" - Boolean, false if the query is not about education/learning
9. "is_supported" - Boolean. False if the user asks for something you cannot do (e.g. generating images, recording audio, writing full essays, or anything that isn't finding an educational video/roadmap).
10. "refusal_message" - If is_supported is false, a friendly 1-sentence explanation of why you can't do that, and what you CAN do instead (find videos and roadmaps).

Return ONLY the JSON object, no markdown formatting, no code blocks, no explanation.
PROMPT;

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->geminiModel}:generateContent?key={$this->geminiApiKey}";

            $response = Http::timeout(60)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 1000,
                    'response_mime_type' => 'application/json',
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $finishReason = $data['candidates'][0]['finishReason'] ?? 'UNKNOWN';

                Log::info('Gemini Analysis Result', [
                    'text' => $text,
                    'finishReason' => $finishReason
                ]);

                // Clean markdown formatting if present
                $text = preg_replace('/```json\s*/', '', $text);
                $text = preg_replace('/```\s*/', '', $text);
                $text = trim($text);

                $parsed = json_decode($text, true);

                if ($parsed && !empty($parsed['topic'])) {
                    // Reject non-educational queries
                    if (isset($parsed['is_valid_educational_topic']) && !$parsed['is_valid_educational_topic']) {
                        return ['topic' => null, 'error' => 'Not an educational topic'];
                    }
                    return $parsed;
                }
            }

            Log::warning('Gemini analysis failed, using fallback', [
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 500),
            ]);

        } catch (Exception $e) {
            Log::warning('Gemini API call failed, using fallback', [
                'error' => $e->getMessage(),
            ]);
        }

        return $this->fallbackAnalysis($query, $gradeLevel);
    }

    /**
     * Simple fallback analysis when Gemini is unavailable.
     */
    private function fallbackAnalysis(string $query, string $gradeLevel): array
    {
        // Strip common prefixes
        $topic = preg_replace('/^(teach me about|explain|what is|how does|tell me about|get me a lesson on|find me a video about|i want to learn about)\s*/i', '', $query);
        $topic = ucfirst(trim($topic));

        if (strlen($topic) < 2) {
            return ['topic' => null];
        }

        return [
            'topic' => $topic,
            'subject' => 'General Science',
            'search_keywords' => explode(' ', $topic),
                    'youtube_search_query' => $topic . ' explained for ' . $gradeLevel . ' students',
            'summary' => null,
            'difficulty_level' => 'intermediate',
            'is_valid_educational_topic' => true,
        ];
    }

    /**
     * Get descriptive context for a grade level (helps Gemini generate better searches).
     */
    private function getGradeLevelContext(string $gradeLevel, string $levelGroup): string
    {
        $contexts = [
            'primary-lower' => 'Early primary school (ages 6-9). Content should be very simple, visual, use animations and songs. Equivalent to UK Key Stage 1.',
            'primary-upper' => 'Upper primary school (ages 9-12). Content should be accessible but can include more detail. Equivalent to UK Key Stage 2.',
            'jhs' => 'Junior High School (ages 12-15). Content should cover curriculum topics in moderate depth. Similar to UK Key Stage 3 / US Middle School.',
            'shs' => 'Senior High School (ages 15-18). Content should be detailed and exam-focused. Similar to UK GCSE/A-Level prep or US High School.',
            'university' => 'University level. Content should be comprehensive and academic.',
        ];

        return $contexts[$levelGroup] ?? 'General education context.';
    }

    /**
     * Generate a standalone topic summary if missing from the main analysis.
     */
    private function getTopicSummary(string $topic, string $gradeLevel, string $levelGroup): ?string
    {
        if (empty($this->geminiApiKey)) return null;

        $context = $this->getGradeLevelContext($gradeLevel, $levelGroup);
        $prompt = "Act as an expert teacher for a student in {$gradeLevel}. Provide a 2-3 paragraph educational summary of the topic: \"{$topic}\". Explain the key concepts clearly and accurately. Context: {$context}. Return ONLY the summary text.";

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->geminiModel}:generateContent?key={$this->geminiApiKey}";
            $response = Http::timeout(30)->post($url, [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

            if ($response->successful()) {
                return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }
        } catch (Exception $e) {
            Log::warning('Fallback summary generation failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Step 2: Check if matching content already exists in the system.
     */
    private function findExistingContent(string $topic, array $keywords, string $gradeLevel, ?string $subject): ?Video
    {
        // Check cache first for recent identical queries
        $cacheKey = 'agent_content_' . md5($topic . $gradeLevel);
        $cachedId = Cache::get($cacheKey);
        if ($cachedId) {
            $video = Video::approved()->find($cachedId);
            if ($video) {
                return $video;
            }
        }

        // Search by title similarity
        $query = Video::approved()
            ->where(function ($q) use ($topic, $keywords) {
                $q->where('title', 'LIKE', '%' . $topic . '%');
                foreach ($keywords as $keyword) {
                    if (strlen($keyword) >= 3) {
                        $q->orWhere('title', 'LIKE', '%' . $keyword . '%');
                    }
                }
            })
            ->where('grade_level', $gradeLevel);

        // Prefer matching subject if available
        if ($subject) {
            $subjectModel = Subject::where('name', 'LIKE', '%' . $subject . '%')->first();
            if ($subjectModel) {
                $query->where(function ($q) use ($subjectModel) {
                    $q->where('subject_id', $subjectModel->id)
                      ->orWhereNull('subject_id');
                });
            }
        }

        $video = $query->orderByDesc('views')->first();

        if ($video) {
            // Cache for 1 hour
            Cache::put($cacheKey, $video->id, 3600);
        }

        return $video;
    }

    /**
     * Step 3: Search YouTube using the existing YouTubeService.
     */
    private function searchYouTube(string $searchQuery, string $gradeLevel): array
    {
        try {
            $results = $this->youtubeService->searchVideos($searchQuery, 8);

            if (!$results || empty($results['items'])) {
                return [];
            }

            $videos = [];
            foreach ($results['items'] as $item) {
                $videoId = $item['id']['videoId'] ?? null;
                if (!$videoId) continue;

                // Check if this YouTube video is already in our system
                $exists = Video::where('external_video_id', $videoId)->exists();
                if ($exists) continue;

                $videos[] = [
                    'videoId' => $videoId,
                    'title' => $item['snippet']['title'] ?? 'Untitled',
                    'description' => $item['snippet']['description'] ?? '',
                    'channelTitle' => $item['snippet']['channelTitle'] ?? 'Unknown',
                    'publishedAt' => $item['snippet']['publishedAt'] ?? null,
                    'thumbnailUrl' => $item['snippet']['thumbnails']['high']['url']
                        ?? $item['snippet']['thumbnails']['default']['url']
                        ?? null,
                ];
            }

            // Enrich with duration info for top candidates
            foreach (array_slice($videos, 0, 5) as $idx => $video) {
                try {
                    $duration = $this->youtubeService->getVideoDuration($video['videoId']);
                    $videos[$idx]['durationSeconds'] = $duration;
                } catch (Exception $e) {
                    $videos[$idx]['durationSeconds'] = null;
                }
            }

            return $videos;

        } catch (Exception $e) {
            Log::error('YouTube search failed in agent', [
                'query' => $searchQuery,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Step 4: Pick the best video from YouTube results.
     */
    private function pickBestVideo(array $videos, array $analysis): ?array
    {
        if (empty($videos)) {
            return null;
        }

        // Filter by duration
        $filtered = array_filter($videos, function ($video) {
            $duration = $video['durationSeconds'] ?? null;
            if ($duration === null) return true; // Keep if we don't know duration
            return $duration >= self::MIN_DURATION_SECONDS && $duration <= self::MAX_DURATION_SECONDS;
        });

        // If all were filtered out, relax constraints
        if (empty($filtered)) {
            $filtered = $videos;
        }

        $filtered = array_values($filtered);

        // Score each video
        $scored = [];
        $topic = strtolower($analysis['topic'] ?? '');
        $keywords = array_map('strtolower', $analysis['search_keywords'] ?? []);

        foreach ($filtered as $video) {
            $score = 0;
            $title = strtolower($video['title']);
            $description = strtolower($video['description'] ?? '');

            // Title contains the topic
            if (str_contains($title, $topic)) {
                $score += 10;
            }

            // Title/description contain keywords
            foreach ($keywords as $keyword) {
                if (strlen($keyword) >= 3) {
                    if (str_contains($title, $keyword)) $score += 3;
                    if (str_contains($description, $keyword)) $score += 1;
                }
            }

            // Prefer educational channel names
            $channel = strtolower($video['channelTitle'] ?? '');
            $eduChannels = ['khan academy', 'crash course', 'ted-ed', 'organic chemistry tutor',
                'science channel', 'national geographic', 'bbc', 'fuse school', 'free school',
                'amoeba sisters', 'peekaboo kidz', 'smart learning', 'education'];
            foreach ($eduChannels as $eduChannel) {
                if (str_contains($channel, $eduChannel)) {
                    $score += 5;
                    break;
                }
            }

            // Ideal duration bonus (5-15 min is ideal for lessons)
            $duration = $video['durationSeconds'] ?? null;
            if ($duration && $duration >= 300 && $duration <= 900) {
                $score += 3;
            }

            // Penalize obviously bad titles
            $badPatterns = ['prank', 'funny', 'fail', 'reaction', 'vlog', 'unboxing', '#shorts'];
            foreach ($badPatterns as $bad) {
                if (str_contains($title, $bad)) {
                    $score -= 10;
                }
            }

            $video['_score'] = $score;
            $scored[] = $video;
        }

        // Sort by score descending
        usort($scored, fn($a, $b) => ($b['_score'] ?? 0) <=> ($a['_score'] ?? 0));

        // Return the highest scoring video
        return $scored[0] ?? null;
    }

    /**
     * Step 5: Create a Video record in the database.
     */
    private function createVideoRecord(array $youtubeVideo, array $analysis, User $user, string $originalQuery): Video
    {
        // Match the subject
        $subjectId = null;
        if (!empty($analysis['subject'])) {
            $subject = Subject::where('name', 'LIKE', '%' . $analysis['subject'] . '%')->first();
            $subjectId = $subject?->id;
        }

        $gradeLevel = $this->formatGradeForDb($user->grade ?? 'primary-1');

        // Build the embed URL
        $embedUrl = 'https://www.youtube.com/embed/' . $youtubeVideo['videoId'];

        // Get duration if not already fetched
        $duration = $youtubeVideo['durationSeconds'] ?? null;
        if (!$duration) {
            try {
                $duration = $this->youtubeService->getVideoDuration($youtubeVideo['videoId']);
            } catch (Exception $e) {
                $duration = null;
            }
        }

        $video = Video::create([
            'title' => html_entity_decode($youtubeVideo['title'], ENT_QUOTES, 'UTF-8'),
            'description' => html_entity_decode($analysis['summary'] ?? ($youtubeVideo['description'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'subject_id' => $subjectId,
            'grade_level' => $gradeLevel,
            'video_source' => 'youtube',
            'external_video_id' => $youtubeVideo['videoId'],
            'external_video_url' => $embedUrl,
            'duration_seconds' => $duration,
            'uploaded_by' => $user->id,
            'status' => 'approved',
            'is_agent_generated' => true,
            'agent_query' => $originalQuery,
            'agent_topic' => $analysis['topic'] ?? null,
        ]);

        Log::info('Agent created video record', [
            'video_id' => $video->id,
            'youtube_id' => $youtubeVideo['videoId'],
            'topic' => $analysis['topic'],
            'grade_level' => $gradeLevel,
            'user_id' => $user->id,
        ]);

        // Cache the result for future queries
        $cacheKey = 'agent_content_' . md5(($analysis['topic'] ?? '') . $gradeLevel);
        Cache::put($cacheKey, $video->id, 3600);

        return $video;
    }

    /**
     * Build the lesson URL using the UrlObfuscator.
     */
    private function buildLessonUrl(Video $video): string
    {
        $encodedId = \App\Services\UrlObfuscator::encode($video->id);
        return route('dashboard.lesson.view', ['lessonId' => $encodedId]);
    }

    /**
     * Get recent agent request history for a user.
     */
    public function getHistory(int $userId, int $limit = 20): array
    {
        return AgentRequest::where('user_id', $userId)
            ->with([
                'video:id,title,thumbnail_path,external_video_id,video_source,duration_seconds',
                'quiz:id,title,quiz_data'
            ])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($request) {
                $hasEssay = false;
                $quizUrl = null;
                if ($request->quiz) {
                    $hasEssay = collect(json_decode($request->quiz->quiz_data, true)['questions'] ?? [])->contains('type', 'essay');
                    $quizUrl = $hasEssay 
                        ? route('quiz.essay', $request->quiz->seo_url)
                        : route('quiz.take', $request->quiz->seo_url);
                }

                // Determine the correct type based on associated data
                $type = $request->type;
                if ($request->quiz_id) {
                    $type = 'quiz';
                } elseif ($request->roadmap_data) {
                    $type = 'roadmap';
                } elseif (!$type) {
                    $type = 'lesson';
                }

                return [
                    'id' => $request->id,
                    'query' => $request->query,
                    'topic' => $request->topic,
                    'status' => $request->status,
                    'type' => $type,
                    'roadmap' => $request->roadmap_data,
                    'video_id' => $request->video_id,
                    'video_title' => $request->video?->title,
                    'thumbnail' => $request->video?->getThumbnailUrl(),
                    'lesson_url' => $request->video ? $this->buildLessonUrl($request->video) : null,
                    'quiz_id' => $request->quiz_id,
                    'quiz_url' => $quizUrl,
                    'quiz_type' => $hasEssay ? 'essay' : 'mcq',
                    'summary' => is_array($request->gemini_response) 
                        ? ($request->gemini_response['summary'] ?? ($request->video?->description ?? null)) 
                        : $request->gemini_response,
                    'created_at' => $request->created_at->diffForHumans(),
                    'processing_time' => $request->processing_time_ms,
                ];
            })
            ->toArray();
    }

    /**
     * Get remaining requests for today.
     */
    public function getRemainingRequests(int $userId): int
    {
        $used = AgentRequest::todayCountForUser($userId);
        return max(0, self::DAILY_LIMIT - $used);
    }

    /**
     * Create a structured learning roadmap with multiple lessons.
     */
    public function findOrCreateRoadmap(string $query, User $user): array
    {
        $startTime = microtime(true);
        $gradeLevel = $user->grade ?? 'Primary 1';
        $levelGroup = $user->current_level_group ?? 'primary-lower';

        // Capture metadata
        $ip = request()->ip();
        $ua = request()->userAgent();

        $agentRequest = AgentRequest::create([
            'user_id' => $user->id,
            'query' => $query,
            'grade_level' => $gradeLevel,
            'level_group' => $levelGroup,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'type' => 'roadmap',
            'status' => 'pending',
        ]);

        // Log to search analytics for trending analysis
        $this->logToSearchAnalytics($query, $user->id);

        try {
            if ($this->isRateLimited($user->id)) {
                $agentRequest->update([
                    'status' => 'failed',
                    'error_message' => 'Daily request limit reached',
                ]);
                return [
                    'success' => false,
                    'message' => 'You\'ve reached your daily limit of ' . self::DAILY_LIMIT . ' AI lesson requests. Try again tomorrow!',
                ];
            }

            // NEW: Step 0 - Check for existing roadmaps to save API quota
            $existingRequest = AgentRequest::where('topic', 'LIKE', '%' . $query . '%')
                ->where('grade_level', $gradeLevel)
                ->where('status', 'created')
                ->whereNotNull('roadmap_data')
                ->latest()
                ->first();

            if ($existingRequest) {
                return [
                    'success' => true,
                    'roadmap' => $existingRequest->roadmap_data,
                    'is_existing' => true,
                    'message' => 'I found an existing GES-aligned roadmap for "' . ($existingRequest->topic ?? $query) . '"!',
                ];
            }

            // Step 1: Analyze Roadmap Query with Gemini
            $agentRequest->update(['status' => 'analyzing']);
            $roadmap = $this->analyzeRoadmapQuery($query, $gradeLevel, $levelGroup);

            if (isset($roadmap['is_supported']) && $roadmap['is_supported'] === false) {
                $agentRequest->update([
                    'status' => 'failed',
                    'error_message' => 'Unsupported roadmap request: ' . ($roadmap['refusal_message'] ?? 'Out of scope'),
                ]);
                return [
                    'success' => false,
                    'message' => $roadmap['refusal_message'] ?? "I'm sorry, I specialize in GES-aligned learning roadmaps. I can't perform that specific request, but I can help you plan your studies!",
                    'is_supported' => false,
                ];
            }

            if (!$roadmap || empty($roadmap['steps'])) {
                throw new Exception('Could not generate roadmap structure');
            }

            $agentRequest->update([
                'topic' => $roadmap['roadmap_title'],
                'subject' => $roadmap['subject'],
                'status' => 'searching',
            ]);

            // Step 2: Curate videos for each step
            $curatedSteps = [];
            foreach ($roadmap['steps'] as $index => $step) {
                // Check if we have existing content first
                $existingVideo = $this->findExistingContent(
                    $step['title'],
                    [],
                    $gradeLevel,
                    $roadmap['subject']
                );

                if ($existingVideo) {
                    $curatedSteps[] = array_merge($step, [
                        'video_id' => $existingVideo->id,
                        'thumbnail' => $existingVideo->getThumbnailUrl(),
                        'lesson_url' => $this->buildLessonUrl($existingVideo),
                        'is_existing' => true,
                    ]);
                } else {
                    // Search YouTube
                    $youtubeResults = $this->searchYouTube($step['youtube_search_query'], $gradeLevel);
                    $bestVideoData = $this->pickBestVideo($youtubeResults, ['topic' => $step['title']]);
                    
                    if ($bestVideoData) {
                        $video = $this->createVideoRecord($bestVideoData, [
                            'topic' => $step['title'],
                            'subject' => $roadmap['subject'],
                            'difficulty_level' => $roadmap['difficulty_level'] ?? 'intermediate'
                        ], $user, $query);

                        $curatedSteps[] = array_merge($step, [
                            'video_id' => $video->id,
                            'thumbnail' => $video->getThumbnailUrl(),
                            'lesson_url' => $this->buildLessonUrl($video),
                            'is_existing' => false,
                        ]);
                    } else {
                        $curatedSteps[] = array_merge($step, [
                            'video_id' => null,
                            'message' => 'No video found for this specific topic yet.',
                        ]);
                    }
                }
            }

            $processingTime = (int)((microtime(true) - $startTime) * 1000);
            $finalRoadmapData = array_merge($roadmap, ['steps' => $curatedSteps]);
            
            $agentRequest->update([
                'status' => 'created',
                'roadmap_data' => $finalRoadmapData,
                'processing_time_ms' => $processingTime,
            ]);

            return [
                'success' => true,
                'roadmap' => $finalRoadmapData,
                'message' => 'Your GES-aligned roadmap for "' . $roadmap['roadmap_title'] . '" is ready!',
            ];

        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            Log::error('Roadmap generation failed', [
                'query' => $query,
                'error' => $errorMsg
            ]);
            
            $agentRequest->update([
                'status' => 'failed',
                'error_message' => $errorMsg,
            ]);

            $friendlyMessage = 'I had trouble creating that roadmap. Please try a different topic!';

            if (str_contains($errorMsg, '429') || str_contains($errorMsg, 'quota')) {
                $friendlyMessage = 'The AI Tutor is currently experiencing high demand. Please try again in about 20 seconds!';
            }

            return [
                'success' => false,
                'message' => $friendlyMessage,
            ];
        }
    }

    /**
     * Create a quiz for the user based on their query.
     */
    public function findOrCreateQuiz(string $query, User $user): array
    {
        $startTime = microtime(true);
        $gradeLevel = $user->grade ?? 'Primary 1';
        $levelGroup = $user->current_level_group ?? 'primary-lower';

        $ip = request()->ip();
        $ua = request()->userAgent();

        $agentRequest = AgentRequest::create([
            'user_id' => $user->id,
            'query' => $query,
            'grade_level' => $gradeLevel,
            'level_group' => $levelGroup,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'status' => 'pending',
        ]);

        $this->logToSearchAnalytics($query, $user->id);

        try {
            if ($this->isRateLimited($user->id)) {
                $agentRequest->update([
                    'status' => 'failed',
                    'error_message' => 'Daily request limit reached',
                ]);
                return [
                    'success' => false,
                    'message' => 'You\'ve reached your daily limit of ' . self::DAILY_LIMIT . ' AI requests. Try again tomorrow!',
                ];
            }
            
            // NEW: Step 0 - Check for existing quizzes first to save API quota
            $existingQuiz = \App\Models\Quiz::where('title', 'LIKE', '%' . $query . '%')
                ->where('grade_level', $gradeLevel)
                ->first();

            if ($existingQuiz) {
                $agentRequest->update([
                    'status' => 'found_existing',
                    'quiz_id' => $existingQuiz->id,
                    'topic' => $existingQuiz->title,
                ]);

                $hasEssay = collect(json_decode($existingQuiz->quiz_data, true)['questions'] ?? [])->contains('type', 'essay');
                $url = $hasEssay 
                    ? route('quiz.essay', $existingQuiz->seo_url)
                    : route('quiz.take', $existingQuiz->seo_url);

                return [
                    'success' => true,
                    'quiz_id' => $existingQuiz->id,
                    'quiz_url' => $url,
                    'topic' => $existingQuiz->title,
                    'type' => 'quiz',
                    'quiz_type' => $hasEssay ? 'essay' : 'mcq',
                    'is_existing' => true,
                    'message' => 'I found an existing quiz on "' . $existingQuiz->title . '" for you!',
                ];
            }

            // Step 1: Generate Quiz with Gemini
            $agentRequest->update(['status' => 'analyzing']);
            $quizData = $this->analyzeQuizQuery($query, $gradeLevel, $levelGroup);

            if (isset($quizData['is_supported']) && $quizData['is_supported'] === false) {
                $agentRequest->update([
                    'status' => 'failed',
                    'error_message' => 'Unsupported quiz request: ' . ($quizData['refusal_message'] ?? 'Out of scope'),
                ]);
                return [
                    'success' => false,
                    'message' => $quizData['refusal_message'] ?? "I'm sorry, I specialize in GES-aligned quizzes. I can't perform that specific request, but I can help you test your knowledge on other topics!",
                    'is_supported' => false,
                ];
            }

            if (!$quizData || empty($quizData['questions'])) {
                throw new Exception('Could not generate quiz content');
            }

            $agentRequest->update([
                'topic' => $quizData['title'],
                'subject' => $quizData['subject'],
                'status' => 'analyzing',
            ]);

            // Step 2: Create the Quiz record
            $subject = Subject::firstOrCreate(['name' => $quizData['subject']]);
            
            $quiz = \App\Models\Quiz::create([
                'title' => $quizData['title'],
                'subject_id' => $subject->id,
                'grade_level' => $gradeLevel,
                'uploaded_by' => $user->id,
                'quiz_data' => json_encode(['questions' => $quizData['questions']]),
                'difficulty_level' => $quizData['difficulty_level'] ?? 'medium',
                'time_limit_minutes' => $quizData['time_limit_minutes'] ?? 15,
                'is_agent_generated' => true,
            ]);

            $processingTime = (int)((microtime(true) - $startTime) * 1000);
            
            $agentRequest->update([
                'status' => 'created',
                'quiz_id' => $quiz->id,
                'processing_time_ms' => $processingTime,
            ]);

            // Determine correct URL based on quiz content
            $hasEssay = collect($quizData['questions'])->contains('type', 'essay');
            $url = $hasEssay 
                ? route('quiz.essay', $quiz->seo_url)
                : route('quiz.take', $quiz->seo_url);

            return [
                'success' => true,
                'quiz_id' => $quiz->id,
                'quiz_url' => $url,
                'topic' => $quiz->title,
                'type' => 'quiz',
                'quiz_type' => $hasEssay ? 'essay' : 'mcq',
                'message' => 'I generated a ' . ($hasEssay ? 'structured essay' : 'multiple-choice') . ' quiz on "' . $quiz->title . '" for you!',
            ];

        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            Log::error('Quiz generation failed', [
                'query' => $query,
                'error' => $errorMsg
            ]);
            
            $agentRequest->update([
                'status' => 'failed',
                'error_message' => $errorMsg,
            ]);

            $friendlyMessage = 'I had trouble creating that quiz. Please try a different topic!';
            
            if (str_contains($errorMsg, '429') || str_contains($errorMsg, 'quota')) {
                $friendlyMessage = 'The AI Tutor is currently experiencing high demand. Please try again in about 20 seconds, or search for a broader topic!';
            } elseif (str_contains($errorMsg, 'timeout')) {
                $friendlyMessage = 'The request took a bit too long. Please try a shorter, more specific topic!';
            }

            return [
                'success' => false,
                'message' => $friendlyMessage,
            ];
        }
    }

    /**
     * Use Gemini to create a structured roadmap based on the GES syllabus.
     */
    private function analyzeRoadmapQuery(string $query, string $gradeLevel, string $levelGroup): ?array
    {
        if (empty($this->geminiApiKey)) {
            return null;
        }

        $gradeLevelContext = $this->getGradeLevelContext($gradeLevel, $levelGroup);

        $prompt = <<<PROMPT
You are an expert curriculum designer for the Ghana Education Service (GES). Create a comprehensive learning roadmap for: "{$query}".
The student is in grade level: {$gradeLevel}.
Context: {$gradeLevelContext}

Your roadmap MUST be strictly based on the current GES syllabus for this subject and level.

Return a JSON object with:
1. "roadmap_title" - A clear title for this roadmap.
2. "subject" - The academic subject (e.g., Mathematics, Science).
3. "description" - A brief 1-2 sentence overview explaining how this aligns with the GES syllabus.
4. "difficulty_level" - "beginner", "intermediate", or "advanced".
5. "steps" - A sequence of 5-7 learning steps. Each step MUST contain:
   - "title" - Short title of the lesson/unit.
   - "description" - What the student will learn in this step.
   - "youtube_search_query" - Optimized search query to find the best educational video for this specific step and grade level.

6. "is_supported" - Boolean. False if they are asking for something you cannot do (like drawing, recording, or tasks that aren't roadmap generation).
7. "refusal_message" - If is_supported is false, a friendly explanation of what you CAN do (find GES roadmaps).

Return ONLY the JSON object, no explanation or markdown.
PROMPT;

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->geminiModel}:generateContent?key={$this->geminiApiKey}";

            $response = Http::timeout(60)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.4,
                    'maxOutputTokens' => 2000,
                    'response_mime_type' => 'application/json',
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                Log::info('Gemini Raw Roadmap Response', ['text' => $text]);

                $text = preg_replace('/```json\s*/', '', $text);
                $text = preg_replace('/```\s*/', '', $text);
                $text = trim($text);

                $decoded = json_decode($text, true);
                if (!$decoded) {
                    Log::error('Gemini Roadmap JSON Decode Failed', ['text' => $text]);
                }
                return $decoded;
            } else {
                Log::error('Gemini Roadmap API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (Exception $e) {
            Log::error('Gemini API Error (Roadmap)', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Use Gemini to create a structured quiz based on the GES syllabus.
     */
    private function analyzeQuizQuery(string $query, string $gradeLevel, string $levelGroup): ?array
    {
        if (empty($this->geminiApiKey)) {
            return null;
        }

        $gradeLevelContext = $this->getGradeLevelContext($gradeLevel, $levelGroup);

        $prompt = <<<PROMPT
You are an expert curriculum developer for the Ghana Education Service (GES).
Create a quiz for a student in: {$gradeLevel}
Context: {$gradeLevelContext}

Based on the student's request: "{$query}"

Instructions:
1. Determine if the student wants a multiple-choice quiz (MCQ), an essay quiz, or a mix. If not specified, default to 5 MCQs.
2. Return ONLY a valid JSON object.
3. The quiz MUST be strictly aligned with the current GES syllabus.
4. The JSON MUST exactly match this structure:
{
    "title": "A short, catchy title for the quiz",
    "subject": "The most relevant subject (e.g. Science, Mathematics, English)",
    "difficulty_level": "easy, medium, or hard",
    "time_limit_minutes": 15,
    "is_supported": true, // False if the request is non-educational or out of scope
    "refusal_message": null, // Explanation if is_supported is false
    "questions": [
        // For MCQ:
        {
            "id": <generate_random_integer>,
            "type": "mcq",
            "question": "The question text (HTML allowed)",
            "preamble": null, // optional context/passage
            "points": 1,
            "options": ["Option A", "Option B", "Option C", "Option D"],
            "correct_answer": 0, // index of the correct option (0-3)
            "has_image": false
        },
        // For Essay:
        {
            "id": <generate_random_integer>,
            "type": "essay",
            "question": "The main essay prompt or instructions (HTML allowed)",
            "preamble": null,
            "points": 5, // total points
            "correct_answer": "A sample reference answer or grading rubric for auto-grading",
            "sub_questions": [ // optional, if the essay has parts a, b, c
                {
                    "id": <generate_random_integer>,
                    "label": "a",
                    "text": "Sub question part a",
                    "sample_answer": "Expected answer for part a",
                    "points": 2
                }
            ]
        }
    ]
}

Return ONLY the JSON object, no explanation or markdown.
PROMPT;

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->geminiModel}:generateContent?key={$this->geminiApiKey}";

            $response = Http::timeout(60)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                ]
            ]);

            if ($response->failed()) {
                throw new Exception('Gemini API request failed: ' . $response->body());
            }

            $result = $response->json();
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            return json_decode($text, true);

        } catch (Exception $e) {
            Log::error('Gemini API Error (Quiz)', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Convert slug grade to display grade for database storage.
     */
    private function formatGradeForDb(string $grade): string
    {
        $map = [
            'primary-1' => 'Primary 1',
            'primary-2' => 'Primary 2',
            'primary-3' => 'Primary 3',
            'primary-4' => 'Primary 4',
            'primary-5' => 'Primary 5',
            'primary-6' => 'Primary 6',
            'jhs-1' => 'JHS 1',
            'jhs-2' => 'JHS 2',
            'jhs-3' => 'JHS 3',
            'shs-1' => 'SHS 1',
            'shs-2' => 'SHS 2',
            'shs-3' => 'SHS 3',
        ];

        return $map[$grade] ?? ucwords(str_replace('-', ' ', $grade));
    }
    /**
     * Log the agent query to SearchAnalytic for trending analysis.
     */
    private function logToSearchAnalytics(string $query, int $userId): void
    {
        try {
            $normalizedQuery = strtolower(trim($query));
            
            $analytic = SearchAnalytic::firstOrNew([
                'query' => $normalizedQuery,
                'domain' => 'agent',
            ]);

            if ($analytic->exists) {
                $analytic->hits += 1;
                $analytic->last_searched_at = now();
            } else {
                $analytic->user_id = $userId;
                $analytic->hits = 1;
                $analytic->last_searched_at = now();
            }

            $analytic->save();
        } catch (Exception $e) {
            Log::error('Failed to log agent query to search analytics', ['error' => $e->getMessage()]);
        }
    }
}
