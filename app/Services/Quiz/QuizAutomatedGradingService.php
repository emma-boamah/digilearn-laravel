<?php

namespace App\Services\Quiz;

use App\Models\QuizAttempt;
use App\Models\Quiz;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QuizAutomatedGradingService
{
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
            ]
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

        return $results;
    }

    /**
     * Analyze a specific part of a response.
     */
    protected function analyzePart($studentAnswer, $modelAnswer, $maxPoints)
    {
        $geminiKey = config('services.gemini.key');
        if ($geminiKey) {
            return $this->analyzeWithGemini($studentAnswer, $modelAnswer, $maxPoints, $geminiKey);
        }

        $apiKey = config('services.openai.key') ?: env('OPENAI_API_KEY');

        if ($apiKey) {
            return $this->analyzeWithAi($studentAnswer, $modelAnswer, $maxPoints, $apiKey);
        }

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
            $response = Http::withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
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
                return [
                    'score' => $content['score'] ?? 0,
                    'feedback' => $content['feedback'] ?? 'AI Review completed.',
                    'strengths' => $content['strengths'] ?? '',
                    'weaknesses' => $content['weaknesses'] ?? ''
                ];
            }
        } catch (\Exception $e) {
            Log::error('AI Grading Failed: ' . $e->getMessage());
        }

        return $this->analyzeWithLocalLogic($studentAnswer, $modelAnswer, $maxPoints);
    }

    /**
     * Google Gemini Agent driver.
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

            $response = Http::post($url, [
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

                return [
                    'score' => $content['score'] ?? 0,
                    'feedback' => $content['feedback'] ?? 'Gemini Review completed.',
                    'strengths' => $content['strengths'] ?? '',
                    'weaknesses' => $content['weaknesses'] ?? ''
                ];
            } else {
                Log::error('Gemini API Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Gemini Grading Failed: ' . $e->getMessage());
        }

        return $this->analyzeWithLocalLogic($studentAnswer, $modelAnswer, $maxPoints);
    }
}
