<?php

namespace App\Services;

use App\Models\CurriculumIndicator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StudentLessonService
{
    protected string $apiKey;
    protected string $model;
    protected LocalLessonSynthesizer $localSynthesizer;

    public function __construct(LocalLessonSynthesizer $localSynthesizer)
    {
        $this->apiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));
        $this->model = config('services.gemini.model', 'gemini-flash-latest');
        $this->localSynthesizer = $localSynthesizer;
    }

    /**
     * Get or generate a structured student lesson for an indicator.
     * Guaranteed never to fail or hang.
     *
     * @return array [
     *   'html' => string,
     *   'markdown' => string,
     *   'source' => string ('textbook'|'ai'|'local'),
     *   'key_takeaways' => array,
     *   'has_textbook' => bool,
     * ]
     */
    public function getLesson(CurriculumIndicator $indicator): array
    {
        // 1. TIER 1: Check for attached textbook sections
        if ($indicator->textbookSections()->exists()) {
            return [
                'html' => '',
                'markdown' => '',
                'source' => 'textbook',
                'key_takeaways' => $indicator->lesson_key_takeaways ?? [],
                'has_textbook' => true,
            ];
        }

        // 2. TIER 2: If we already have a cached AI lesson, return it instantly
        if (!empty($indicator->student_lesson_html) && $indicator->lesson_source === 'ai') {
            return [
                'html' => $indicator->student_lesson_html,
                'markdown' => $indicator->student_lesson_markdown ?? '',
                'source' => 'ai',
                'key_takeaways' => $indicator->lesson_key_takeaways ?? [],
                'has_textbook' => false,
            ];
        }

        // 3. TIER 3: Attempt AI Lesson Generation (enriches and upgrades any local lesson)
        if (!empty($this->apiKey)) {
            try {
                $aiLesson = $this->generateWithGemini($indicator);
                if (!empty($aiLesson['html'])) {
                    // Cache in database
                    $indicator->update([
                        'student_lesson_markdown' => $aiLesson['markdown'],
                        'student_lesson_html' => $aiLesson['html'],
                        'lesson_source' => 'ai',
                        'lesson_key_takeaways' => $aiLesson['key_takeaways'],
                        'lesson_generated_at' => now(),
                    ]);

                    return [
                        'html' => $aiLesson['html'],
                        'markdown' => $aiLesson['markdown'],
                        'source' => 'ai',
                        'key_takeaways' => $aiLesson['key_takeaways'],
                        'has_textbook' => false,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning("AI Lesson generation failed for indicator {$indicator->id} ({$e->getMessage()}). Using local synthesizer fallback.");
            }
        }

        // 4. If we already have a cached local lesson (and AI was unavailable), return it
        if (!empty($indicator->student_lesson_html)) {
            return [
                'html' => $indicator->student_lesson_html,
                'markdown' => $indicator->student_lesson_markdown ?? '',
                'source' => $indicator->lesson_source ?? 'local',
                'key_takeaways' => $indicator->lesson_key_takeaways ?? [],
                'has_textbook' => false,
            ];
        }

        // 5. TIER 5: Deterministic Local Synthesizer Fallback (Zero AI, instant, offline)
        return $this->generateAndCacheLocalLesson($indicator);
    }

    /**
     * Synthesize and cache lesson locally using the deterministic synthesizer.
     */
    public function generateAndCacheLocalLesson(CurriculumIndicator $indicator): array
    {
        $localLesson = $this->localSynthesizer->synthesize($indicator);

        // Cache local lesson so page loads instantly
        $indicator->update([
            'student_lesson_markdown' => $localLesson['markdown'],
            'student_lesson_html' => $localLesson['html'],
            'lesson_source' => 'local',
            'lesson_key_takeaways' => $localLesson['key_takeaways'],
            'lesson_generated_at' => now(),
        ]);

        return [
            'html' => $localLesson['html'],
            'markdown' => $localLesson['markdown'],
            'source' => 'local',
            'key_takeaways' => $localLesson['key_takeaways'],
            'has_textbook' => false,
        ];
    }

    /**
     * Generate student-friendly lesson using Google Gemini with multi-model fallback.
     */
    protected function generateWithGemini(CurriculumIndicator $indicator): array
    {
        $subStrand = $indicator->subStrand;
        $strand = $subStrand?->strand;
        $curriculum = $strand?->curriculum;
        $subjectName = $curriculum?->subject?->name ?? 'Mathematics';
        $gradeLabel = $strand?->grade_label ?? 'JHS';

        $prompt = <<<PROMPT
You are an expert, engaging school tutor explaining a curriculum checkpoint to a {$gradeLabel} student in {$subjectName}.
Topic: "{$indicator->title}"
Code: {$indicator->indicator_code}
Syllabus Context: Strand: "{$strand?->title}", Sub-strand: "{$subStrand?->title}"
Exemplar tasks from curriculum:
{$indicator->exemplars}

Write a comprehensive, deeply informative, and engaging student lesson formatted in GitHub Markdown (use LaTeX notation $...$ for inline formulas, and $$...$$ for display formulas where applicable):
1. **The Big Idea**: Explain the concept thoroughly with relatable real-world analogies, why it was developed, and how it impacts modern daily life.
2. **Core Concepts & Deep Dive**: Provide a clear, detailed breakdown of the principles, components, mechanisms, rules, or architectural milestones involved. Don't be brief—explain the "why" and "how".
3. **Step-by-Step Practical Examples / Worked Solutions**: Walk through 2 to 3 detailed, solved real-world scenarios or exercises based on the syllabus exemplars, breaking down each step with clear explanations.
4. **Common Mistakes & Exam Tips**: Highlight 2 to 3 common pitfalls or misconceptions students make on this topic and how to avoid them in exams.
5. **Key Takeaways**: 4 concise bullet points summarizing the essential facts learned.

Do NOT sound like a teacher's syllabus or exam rubric. Write directly to the student in an encouraging, crystal-clear, and informative tone.
PROMPT;

        $modelsToTry = [
            'gemini-flash-lite-latest',
            'gemini-3.5-flash-lite',
            'gemini-2.5-flash-lite',
            'gemini-3.8-flash',
            'gemini-flash-latest',
        ];

        if (!empty($this->model) && !in_array($this->model, $modelsToTry)) {
            array_unshift($modelsToTry, $this->model);
        }
        $modelsToTry = array_values(array_unique($modelsToTry));

        $rawMarkdown = null;
        $lastError = null;

        foreach ($modelsToTry as $modelName) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$this->apiKey}";
            try {
                $response = Http::timeout(30)
                    ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                    ->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.4,
                            'maxOutputTokens' => 2500,
                        ]
                    ]);

                if ($response->successful()) {
                    $body = $response->json();
                    $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    if (!empty($text)) {
                        $rawMarkdown = $text;
                        break;
                    }
                } else {
                    $lastError = "Gemini error (HTTP {$response->status()} on {$modelName})";
                }
            } catch (\Throwable $e) {
                $lastError = "Gemini exception on {$modelName}: " . $e->getMessage();
            }
        }

        if (empty($rawMarkdown)) {
            throw new \Exception($lastError ?? "All Gemini models failed or returned empty response.");
        }

        // Clean markdown fences if any
        $cleanMarkdown = trim(preg_replace('/^```(?:markdown)?|```$/m', '', $rawMarkdown));
        $html = Str::markdown($cleanMarkdown);

        // Extract takeaways
        $takeaways = [];
        if (preg_match('/(?:Key Takeaways|Summary|What You Learned)[:\s]*(.*?)(?:\n\n|\z)/is', $cleanMarkdown, $tm)) {
            preg_match_all('/^[\*\-]\s*(.+)$/m', $tm[1], $matches);
            if (!empty($matches[1])) {
                $takeaways = array_slice($matches[1], 0, 4);
            }
        }

        if (empty($takeaways)) {
            $takeaways = [
                "Mastered the core concepts of {$indicator->student_title}.",
                "Learned how to solve problems step-by-step.",
                "Ready to complete the checkpoint practice and quizzes.",
            ];
        }

        return [
            'markdown' => $cleanMarkdown,
            'html' => $html,
            'key_takeaways' => $takeaways,
        ];
    }
}
