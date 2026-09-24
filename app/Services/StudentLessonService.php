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

        // 2. TIER 2: Check for cached lesson in database
        if (!empty($indicator->student_lesson_html)) {
            $subjectName = $indicator->subStrand?->strand?->curriculum?->subject?->name ?? '';
            $isNotMath = !empty($subjectName) && !str_contains(strtolower($subjectName), 'math');

            // Auto-heal: If an indicator belonging to a non-math subject was previously cached
            // with the legacy fallback text ("mathematical foundation"), regenerate it properly.
            if ($isNotMath && str_contains($indicator->student_lesson_html, 'mathematical foundation')) {
                return $this->generateAndCacheLocalLesson($indicator);
            }

            return [
                'html' => $indicator->student_lesson_html,
                'markdown' => $indicator->student_lesson_markdown ?? '',
                'source' => $indicator->lesson_source ?? 'ai',
                'key_takeaways' => $indicator->lesson_key_takeaways ?? [],
                'has_textbook' => false,
            ];
        }

        // 3. TIER 3: Attempt AI Lesson Generation
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
                Log::warning("AI Lesson generation failed for indicator {$indicator->id} ({$e->getMessage()}). Falling back to local synthesizer.");
            }
        }

        // 4. TIER 4: Deterministic Local Synthesizer Fallback (Zero AI, instant, offline)
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
     * Generate student-friendly lesson using Google Gemini.
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

Write a comprehensive, engaging student lesson formatted in GitHub Markdown (with LaTeX math notation \$...$ or \$\$...\$\$ where applicable):
1. **The Big Idea**: Explain the concept clearly and why it matters in real life (use relatable analogies).
2. **Step-by-Step Rules & Methods**: Break down the core concepts, procedures, architectural features, or rules in a clear, sequential list.
3. **Step-by-Step Worked Examples**: Walk through 2 to 3 detailed examples, practical scenarios, or solved exercises based on the syllabus exemplars, showing each step clearly.
4. **Key Takeaways**: 3 to 4 concise bullet points summarizing what was learned.

Do NOT sound like a teacher's syllabus or exam rubric. Write directly to the student in an encouraging, crystal-clear tone.
PROMPT;

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::timeout(15)->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 2048,
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception("Gemini API error (Status: {$response->status()}): " . $response->body());
        }

        $body = $response->json();
        $rawMarkdown = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($rawMarkdown)) {
            throw new \Exception("Gemini returned empty response.");
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
