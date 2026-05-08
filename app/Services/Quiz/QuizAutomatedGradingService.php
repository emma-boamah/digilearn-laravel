<?php

namespace App\Services\Quiz;

use App\Models\QuizAttempt;
use App\Models\Quiz;
use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QuizAutomatedGradingService
{
    /**
     * Cache key for the Gemini circuit breaker cooldown.
     */
    protected const GEMINI_COOLDOWN_KEY = 'gemini_api_rate_limited';

    /**
     * Cache key for the OpenAI circuit breaker cooldown.
     */
    protected const OPENAI_COOLDOWN_KEY = 'openai_api_rate_limited';

    /**
     * How long (in seconds) to wait before retrying after a rate limit.
     */
    protected const COOLDOWN_SECONDS = 60;

    /**
     * Cache key to prevent duplicate admin notifications.
     */
    protected const GEMINI_NOTIFIED_KEY = 'gemini_rate_limit_notified';
    protected const OPENAI_NOTIFIED_KEY = 'openai_rate_limit_notified';

    /**
     * Track whether AI was used or fell back during this request.
     */
    protected string $gradingDriver = 'unknown';

    /**
     * Get the driver that was used for grading.
     */
    public function getGradingDriver(): string
    {
        return $this->gradingDriver;
    }

    /**
     * Check if the Gemini API is currently rate-limited.
     */
    public function isGeminiRateLimited(): bool
    {
        return Cache::has(self::GEMINI_COOLDOWN_KEY);
    }

    /**
     * Get the remaining cooldown time in seconds for Gemini.
     */
    public function geminiCooldownRemaining(): int
    {
        if (!$this->isGeminiRateLimited()) {
            return 0;
        }
        $expiresAt = Cache::get(self::GEMINI_COOLDOWN_KEY);
        return max(0, $expiresAt - now()->timestamp);
    }

    /**
     * Generate suggested marks and analysis for an essay attempt.
     *
     * @param QuizAttempt $attempt
     * @return array
     */
    public function suggestMarks(QuizAttempt $attempt)
    {
        $quiz = Quiz::find($attempt->quiz_id);
        if (!$quiz) return [];

        $quizData = json_decode($quiz->quiz_data, true);
        $questions = $quizData['questions'] ?? [];
        $userAnswers = is_array($attempt->answers) ? $attempt->answers : json_decode($attempt->answers ?? '[]', true);

        $results = [
            'marks' => [],
            'feedback' => [],
            'strengths' => [],
            'weaknesses' => [],
            'analysis' => [
                'strengths' => [],
                'weaknesses' => []
            ],
            'grading_driver' => 'local', // Default, updated below
            'rate_limited' => false,
        ];

        foreach ($questions as $qIdx => $question) {
            if (($question['type'] ?? 'mcq') !== 'essay') continue;

            $userResponse = $userAnswers[$qIdx] ?? '';
            
            if (!empty($question['sub_questions'])) {
                foreach ($question['sub_questions'] as $sIdx => $sub) {
                    $subResponse = is_array($userResponse) ? ($userResponse[$sIdx] ?? '') : ($sIdx == 0 ? $userResponse : '');
                    $analysis = $this->analyzePart($subResponse, $sub['sample_answer'] ?? '', $sub['points'] ?? 1);
                    
                    $results['marks']["{$qIdx}_{$sIdx}"] = $analysis['score'];
                    $results['feedback']["{$qIdx}_{$sIdx}"] = $analysis['feedback'];
                    $results['strengths']["{$qIdx}_{$sIdx}"] = $analysis['strengths'];
                    $results['weaknesses']["{$qIdx}_{$sIdx}"] = $analysis['weaknesses'];
                    
                    if (!empty($analysis['strengths'])) $results['analysis']['strengths'][] = $analysis['strengths'];
                    if (!empty($analysis['weaknesses'])) $results['analysis']['weaknesses'][] = $analysis['weaknesses'];
                }
            } else {
                $analysis = $this->analyzePart($userResponse, $question['correct_answer'] ?? '', $question['points'] ?? 10);
                $results['marks'][$qIdx] = $analysis['score'];
                $results['feedback'][$qIdx] = $analysis['feedback'];
                $results['strengths'][$qIdx] = $analysis['strengths'];
                $results['weaknesses'][$qIdx] = $analysis['weaknesses'];
                
                if (!empty($analysis['strengths'])) $results['analysis']['strengths'][] = $analysis['strengths'];
                if (!empty($analysis['weaknesses'])) $results['analysis']['weaknesses'][] = $analysis['weaknesses'];
            }
        }

        $results['grading_driver'] = $this->gradingDriver;
        $results['rate_limited'] = $this->isGeminiRateLimited();

        return $results;
    }

    /**
     * Analyze a specific part of a response.
     */
    protected function analyzePart($studentAnswer, $modelAnswer, $maxPoints)
    {
        // --- Circuit Breaker: Skip API calls if rate-limited ---
        $geminiKey = config('services.gemini.key');
        if ($geminiKey && !$this->isGeminiRateLimited()) {
            return $this->analyzeWithGemini($studentAnswer, $modelAnswer, $maxPoints, $geminiKey);
        }

        if ($geminiKey && $this->isGeminiRateLimited()) {
            Log::info('Gemini API is rate-limited, falling back to local grading. Cooldown: ' . $this->geminiCooldownRemaining() . 's remaining.');
            $this->gradingDriver = 'local (gemini rate-limited)';
            return $this->analyzeWithLocalLogic($studentAnswer, $modelAnswer, $maxPoints);
        }

        $apiKey = config('services.openai.key') ?: env('OPENAI_API_KEY');

        if ($apiKey && !Cache::has(self::OPENAI_COOLDOWN_KEY)) {
            return $this->analyzeWithAi($studentAnswer, $modelAnswer, $maxPoints, $apiKey);
        }

        $this->gradingDriver = 'local';
        return $this->analyzeWithLocalLogic($studentAnswer, $modelAnswer, $maxPoints);
    }

    /**
     * Fallback driver: Keyword & Complexity analysis.
     */
    protected function analyzeWithLocalLogic($studentAnswer, $modelAnswer, $maxPoints)
    {
        $sClean = strtolower(strip_tags($studentAnswer));
        $mClean = strtolower(strip_tags($modelAnswer));

        if (empty($sClean)) {
            return [
                'score' => 0,
                'feedback' => 'No response provided.',
                'strengths' => '',
                'weaknesses' => 'Question was left blank.'
            ];
        }

        // Basic keyword matching
        $mWords = array_unique(explode(' ', preg_replace('/[^\w\s]/', '', $mClean)));
        $sWords = array_unique(explode(' ', preg_replace('/[^\w\s]/', '', $sClean)));

        // Remove common stop words (simplified)
        $stopWords = ['the', 'is', 'at', 'which', 'on', 'and', 'a', 'an', 'of', 'for', 'it', 'in'];
        $mKeywords = array_filter($mWords, fn($w) => strlen($w) > 3 && !in_array($w, $stopWords));
        
        if (empty($mKeywords)) {
            // If no marking scheme provided, give baseline for length
            $score = min($maxPoints, count($sWords) > 10 ? ($maxPoints * 0.5) : 0);
            return [
                'score' => $score,
                'feedback' => 'Automated check: Response submitted. Manual review recommended as no marking scheme was provided.',
                'strengths' => count($sWords) > 10 ? 'Detailed response length.' : '',
                'weaknesses' => 'Missing marking scheme for deeper analysis.'
            ];
        }

        $matches = array_intersect($mKeywords, $sWords);
        $ratio = count($mKeywords) > 0 ? count($matches) / count($mKeywords) : 0;
        
        $score = round($ratio * $maxPoints, 1);
        
        // Strength/Weakness generation
        $missed = array_diff($mKeywords, $sWords);
        $strength = count($matches) > 0 ? "You correctly identified concepts like: " . implode(', ', array_slice($matches, 0, 3)) : "";
        $weakness = count($missed) > 0 ? "Try to include terms such as: " . implode(', ', array_slice($missed, 0, 3)) : "";

        return [
            'score' => $score,
            'feedback' => "Automated suggest based on keyword match ($score/$maxPoints). Match ratio: " . round($ratio * 100) . "%.",
            'strengths' => $strength,
            'weaknesses' => $weakness
        ];
    }

    /**
     * Future-proof: AI Agent driver.
     */
    protected function analyzeWithAi($studentAnswer, $modelAnswer, $maxPoints, $apiKey)
    {
        try {
            $response = Http::timeout(30)->withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional educational assessor. Grade the student response against the marking scheme. Address the student directly using 2nd-person pronouns (e.g., "You stated...", "Your answer..."). Do NOT use 3rd-person pronouns like "The student". Return ONLY JSON.'],
                    ['role' => 'user', 'content' => "Marking Scheme: $modelAnswer\nStudent Response: $studentAnswer\nMax Points: $maxPoints\nReturn JSON: {score: float, feedback: string, strengths: string, weaknesses: string}"]
                ],
                'response_format' => ['type' => 'json_object']
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = json_decode($data['choices'][0]['message']['content'], true);
                $this->gradingDriver = 'openai';
                return [
                    'score' => $content['score'] ?? 0,
                    'feedback' => $content['feedback'] ?? 'AI Review completed.',
                    'strengths' => $content['strengths'] ?? '',
                    'weaknesses' => $content['weaknesses'] ?? ''
                ];
            }

            // Rate limit or quota exceeded
            if ($response->status() === 429) {
                $retryAfter = (int) ($response->header('Retry-After') ?: self::COOLDOWN_SECONDS);
                Cache::put(self::OPENAI_COOLDOWN_KEY, now()->timestamp + $retryAfter, $retryAfter);
                Log::warning("OpenAI API rate-limited. Cooldown set for {$retryAfter}s.", [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 300),
                ]);

                // Notify super admins (once per cooldown period)
                $this->notifyAdminsOfRateLimit('OpenAI', $retryAfter, self::OPENAI_NOTIFIED_KEY);
            } else {
                Log::error('OpenAI API Error: ' . $response->status() . ' - ' . substr($response->body(), 0, 300));
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('OpenAI API Connection Timeout: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('AI Grading Failed: ' . $e->getMessage());
        }

        $this->gradingDriver = 'local (openai fallback)';
        return $this->analyzeWithLocalLogic($studentAnswer, $modelAnswer, $maxPoints);
    }

    /**
     * Google Gemini Agent driver with circuit breaker protection.
     */
    protected function analyzeWithGemini($studentAnswer, $modelAnswer, $maxPoints, $apiKey)
    {
        try {
            $model = config('services.gemini.model', 'gemini-1.5-flash');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $prompt = "You are a professional educational assessor. Grade the student response against the marking scheme.\n" .
                      "IMPORTANT: Address the student directly using 2nd-person pronouns (e.g., 'You stated...', 'Your answer...'). Do NOT use 3rd-person pronouns like 'The student'.\n\n" .
                      "Marking Scheme: $modelAnswer\n" .
                      "Student Response: $studentAnswer\n" .
                      "Max Points: $maxPoints\n\n" .
                      "Provide your evaluation in JSON format with the following keys:\n" .
                      "- score: (float) The marks awarded out of $maxPoints\n" .
                      "- feedback: (string) A brief explanation of the grade addressed directly to the student\n" .
                      "- strengths: (string) What they did well, addressed directly to the student\n" .
                      "- weaknesses: (string) Areas for improvement, addressed directly to the student";

            $response = Http::timeout(30)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json'
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $jsonString = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                $content = json_decode($jsonString, true);

                $this->gradingDriver = 'gemini';
                return [
                    'score' => $content['score'] ?? 0,
                    'feedback' => $content['feedback'] ?? 'Gemini Review completed.',
                    'strengths' => $content['strengths'] ?? '',
                    'weaknesses' => $content['weaknesses'] ?? ''
                ];
            }

            // --- Circuit Breaker: Detect rate limit / quota exhaustion ---
            $status = $response->status();
            $body = $response->body();

            if ($status === 429 || str_contains($body, 'RESOURCE_EXHAUSTED') || str_contains($body, 'quota')) {
                $retryAfter = (int) ($response->header('Retry-After') ?: self::COOLDOWN_SECONDS);
                Cache::put(self::GEMINI_COOLDOWN_KEY, now()->timestamp + $retryAfter, $retryAfter);

                Log::warning("Gemini API rate-limited (HTTP {$status}). Circuit breaker activated for {$retryAfter}s.", [
                    'status' => $status,
                    'body' => substr($body, 0, 300),
                ]);

                // Notify super admins (once per cooldown period)
                $this->notifyAdminsOfRateLimit('Gemini', $retryAfter, self::GEMINI_NOTIFIED_KEY);
            } else {
                Log::error('Gemini API Error: HTTP ' . $status . ' - ' . substr($body, 0, 300));
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Gemini API Connection Timeout: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Gemini Grading Failed: ' . $e->getMessage());
        }

        $this->gradingDriver = 'local (gemini fallback)';
        return $this->analyzeWithLocalLogic($studentAnswer, $modelAnswer, $maxPoints);
    }

    /**
     * Send a one-time in-app notification to all super admins about a rate limit event.
     */
    protected function notifyAdminsOfRateLimit(string $provider, int $cooldownSeconds, string $notifiedCacheKey): void
    {
        // Only send one notification per cooldown window
        if (Cache::has($notifiedCacheKey)) {
            return;
        }

        Cache::put($notifiedCacheKey, true, $cooldownSeconds);

        try {
            $minutes = ceil($cooldownSeconds / 60);
            $admins = User::where('is_superuser', true)->get();

            foreach ($admins as $admin) {
                $admin->notify(new AdminNotification(
                    "⚠️ {$provider} AI Rate Limit Reached",
                    "{$provider} API token quota has been exhausted. AI-powered essay grading is temporarily using keyword-based fallback. Service will automatically resume in approximately {$minutes} minute(s). No action is required — pending essays can be reviewed manually if needed.",
                    url('/admin/quizzes/review')
                ));
            }

            Log::info("Admin notification sent for {$provider} rate limit. Cooldown: {$cooldownSeconds}s.");
        } catch (\Exception $e) {
            Log::error("Failed to notify admins about {$provider} rate limit: " . $e->getMessage());
        }
    }
}
