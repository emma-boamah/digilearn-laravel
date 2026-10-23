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
            $quiz = $this->getOrGenerateQuiz($indicator);
            $segments = $this->getOrGenerateSegments($indicator);
            return [
                'html' => '',
                'markdown' => '',
                'source' => 'textbook',
                'key_takeaways' => $indicator->lesson_key_takeaways ?? [],
                'quiz' => $quiz,
                'interactive_segments' => $segments,
                'has_textbook' => true,
            ];
        }

        // 2. TIER 2: If we already have a cached AI lesson, return it instantly
        if (!empty($indicator->student_lesson_html) && $indicator->lesson_source === 'ai') {
            $quiz = $this->getOrGenerateQuiz($indicator);
            $segments = $this->getOrGenerateSegments($indicator);
            return [
                'html' => self::cleanMathTypography($indicator->student_lesson_html),
                'markdown' => $indicator->student_lesson_markdown ?? '',
                'source' => 'ai',
                'key_takeaways' => self::formatTakeaways($indicator->lesson_key_takeaways ?? []),
                'quiz' => $quiz,
                'interactive_segments' => $segments,
                'has_textbook' => false,
            ];
        }

        // 3. TIER 3: Attempt AI Lesson Generation (enriches and upgrades any local lesson)
        if (!empty($this->apiKey)) {
            try {
                $aiLesson = $this->generateWithGemini($indicator);
                if (!empty($aiLesson['html'])) {
                    $cleanedHtml = self::cleanMathTypography($aiLesson['html']);
                    $formattedTakeaways = self::formatTakeaways($aiLesson['key_takeaways']);
                    $quiz = !empty($aiLesson['quiz']) ? $aiLesson['quiz'] : $this->localSynthesizer->synthesizeQuiz($indicator, $indicator->student_title, $indicator->subStrand?->strand?->curriculum?->subject?->name ?? 'Mathematics', $formattedTakeaways);
                    $segments = $this->getOrGenerateSegments($indicator);

                    // Cache in database
                    $indicator->update([
                        'student_lesson_markdown' => $aiLesson['markdown'],
                        'student_lesson_html' => $cleanedHtml,
                        'lesson_source' => 'ai',
                        'lesson_key_takeaways' => $formattedTakeaways,
                        'lesson_quiz' => $quiz,
                        'interactive_segments' => $segments,
                        'lesson_generated_at' => now(),
                    ]);

                    return [
                        'html' => $cleanedHtml,
                        'markdown' => $aiLesson['markdown'],
                        'source' => 'ai',
                        'key_takeaways' => $formattedTakeaways,
                        'quiz' => $quiz,
                        'interactive_segments' => $segments,
                        'has_textbook' => false,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning("AI Lesson generation failed for indicator {$indicator->id} ({$e->getMessage()}). Using local synthesizer fallback.");
            }
        }

        // 4. If we already have a cached local lesson (and AI was unavailable), return it
        if (!empty($indicator->student_lesson_html)) {
            $quiz = $this->getOrGenerateQuiz($indicator);
            $segments = $this->getOrGenerateSegments($indicator);
            return [
                'html' => self::cleanMathTypography($indicator->student_lesson_html),
                'markdown' => $indicator->student_lesson_markdown ?? '',
                'source' => $indicator->lesson_source ?? 'local',
                'key_takeaways' => self::formatTakeaways($indicator->lesson_key_takeaways ?? []),
                'quiz' => $quiz,
                'interactive_segments' => $segments,
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
        $cleanHtml = self::cleanMathTypography($localLesson['html']);
        $formattedTakeaways = self::formatTakeaways($localLesson['key_takeaways']);
        $quiz = $localLesson['quiz'] ?? $this->localSynthesizer->synthesizeQuiz($indicator, $indicator->student_title, $indicator->subStrand?->strand?->curriculum?->subject?->name ?? 'Mathematics', $formattedTakeaways);
        $segments = $localLesson['interactive_segments'] ?? $this->getOrGenerateSegments($indicator);

        // Cache local lesson so page loads instantly
        $indicator->update([
            'student_lesson_markdown' => $localLesson['markdown'],
            'student_lesson_html' => $cleanHtml,
            'lesson_source' => 'local',
            'lesson_key_takeaways' => $formattedTakeaways,
            'lesson_quiz' => $quiz,
            'interactive_segments' => $segments,
            'lesson_generated_at' => now(),
        ]);

        return [
            'html' => $cleanHtml,
            'markdown' => $localLesson['markdown'],
            'source' => 'local',
            'key_takeaways' => $formattedTakeaways,
            'quiz' => $quiz,
            'interactive_segments' => $segments,
            'has_textbook' => false,
        ];
    }

    /**
     * Ensure indicator has a checkpoint quiz, generating one deterministically if missing.
     */
    public function getOrGenerateQuiz(CurriculumIndicator $indicator): array
    {
        if (!empty($indicator->lesson_quiz) && is_array($indicator->lesson_quiz)) {
            return $indicator->lesson_quiz;
        }

        $subjectName = $indicator->subStrand?->strand?->curriculum?->subject?->name ?? 'Mathematics';
        $takeaways = $indicator->lesson_key_takeaways ?? [];
        $quiz = $this->localSynthesizer->synthesizeQuiz($indicator, $indicator->student_title, $subjectName, $takeaways);

        $indicator->update(['lesson_quiz' => $quiz]);

        return $quiz;
    }

    /**
     * Ensure indicator has interactive segments, synthesizing them if missing.
     */
    public function getOrGenerateSegments(CurriculumIndicator $indicator): array
    {
        if (!empty($indicator->interactive_segments) && is_array($indicator->interactive_segments) && count($indicator->interactive_segments) > 0) {
            return $indicator->interactive_segments;
        }

        $localLesson = $this->localSynthesizer->synthesize($indicator);
        $segments = $localLesson['interactive_segments'] ?? [];

        if (!empty($segments)) {
            $indicator->update(['interactive_segments' => $segments]);
        }

        return $segments;
    }

    /**
     * Clean up LaTeX notation into clean student-friendly typography for numbers, symbols, and arrows.
     */
    public static function cleanMathTypography(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // 1. Directional and logical arrows
        $html = preg_replace('/\$?\\\\(?:rightarrow|to|longrightarrow)\$?/', ' &rarr; ', $html);
        $html = preg_replace('/\$?\\\\(?:leftarrow|longleftarrow)\$?/', ' &larr; ', $html);
        $html = preg_replace('/\$?\\\\(?:leftrightarrow)\$?/', ' &harr; ', $html);
        $html = preg_replace('/\$?\\\\(?:Rightarrow)\$?/', ' &rArr; ', $html);
        $html = preg_replace('/\$?\\\\(?:Leftarrow)\$?/', ' &lArr; ', $html);
        $html = preg_replace('/\$?\\\\(?:Leftrightarrow)\$?/', ' &hArr; ', $html);

        // 2. Comparison and operators wrapped in dollars ($>, $<, $=, $\le$, $\ge$, etc.)
        $html = preg_replace('/\$(&gt;|>)\$/', '&gt;', $html);
        $html = preg_replace('/\$(&lt;|<)\$/', '&lt;', $html);
        $html = preg_replace('/\$(=)\$/', '=', $html);
        $html = preg_replace('/\$?\\\\(?:le|leq)\$?/', '&le;', $html);
        $html = preg_replace('/\$?\\\\(?:ge|geq)\$?/', '&ge;', $html);
        $html = preg_replace('/\$?\\\\(?:ne|neq)\$?/', '&ne;', $html);
        $html = preg_replace('/\$?\\\\(?:times)\$?/', '&times;', $html);
        $html = preg_replace('/\$?\\\\(?:div)\$?/', '&divide;', $html);
        $html = preg_replace('/\$?\\\\(?:approx)\$?/', '&asymp;', $html);
        $html = preg_replace('/\$?\\\\(?:pm)\$?/', '&plusmn;', $html);

        // 3. Isolated plain numbers wrapped in dollars (e.g. $1$, $100$, $1,000,000,000$)
        $html = preg_replace('/\$([0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]+)?)\$/', '$1', $html);

        // 4. Transform any escaped or code-wrapped figure span/captions into clean styled elements
        $html = preg_replace_callback('/(?:<pre\b[^>]*><code\b[^>]*>|<p>)\s*(?:&lt;|<)span\s+class=(?:&quot;|["\'])text-xs text-slate-500[^>"\'&]*?(?:&quot;|["\'])(?:&gt;|>)([\s\S]*?)(?:&lt;|<)\/span(?:&gt;|>)\s*(?:<\/code><\/pre>|<\/p>)/i', function ($m) {
            $caption = strip_tags(html_entity_decode($m[1]));
            return '<span class="text-xs text-slate-500 dark:text-slate-400 mt-2 font-medium text-center block">' . $caption . '</span>';
        }, $html);

        return $html;
    }

    /**
     * Format a single takeaway string with clean bold/italic tags and math typography.
     */
    public static function formatTakeaway(string $text): string
    {
        // Convert **bold** to <strong>bold</strong>
        $html = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
        // Convert *italic* to <em>italic</em>
        $html = preg_replace('/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $html);
        // Clean math typography (arrows, operators, plain numbers)
        return self::cleanMathTypography($html);
    }

    /**
     * Format an array of takeaway strings.
     */
    public static function formatTakeaways(array $takeaways): array
    {
        return array_map([self::class, 'formatTakeaway'], $takeaways);
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

Write a comprehensive, deeply informative, and engaging student lesson formatted in GitHub Markdown.
Typography Rules:
- Write plain numbers and values directly as clean text without dollar signs (e.g. write "100", "1,000,000,000", "billions" - NEVER wrap normal numbers in $).
- Use standard symbols for arrows (→) and comparisons (>, <, =, ≤, ≥).
- Reserve LaTeX $...$ or $$...$$ exclusively for actual algebraic equations or complex formulas (e.g. \$x^2 + \$y^2 = r^2, $\frac{a}{b}$).

Structure:
1. **The Big Idea**: Explain the concept thoroughly with relatable real-world analogies, why it was developed, and how it impacts modern daily life.
2. **Core Concepts & Deep Dive**: Provide a clear, detailed breakdown of the principles, components, mechanisms, rules, or architectural milestones involved. Don't be brief—explain the "why" and "how".
3. **Step-by-Step Practical Examples / Worked Solutions**: Walk through 2 to 3 detailed, solved real-world scenarios or exercises based on the syllabus exemplars, breaking down each step with clear explanations.
4. **Common Mistakes & Exam Tips**: Highlight 2 to 3 common pitfalls or misconceptions students make on this topic and how to avoid them in exams.
5. **Key Takeaways**: 4 concise bullet points summarizing the essential facts learned.
6. **Checkpoint Quiz**: End with a JSON codeblock containing 3 multiple-choice questions to test comprehension:
```json
[
  {
    "id": 1,
    "question": "Clear question text?",
    "options": ["Option A", "Option B", "Option C", "Option D"],
    "correct_answer": 0,
    "explanation": "Brief student explanation of why this answer is correct."
  }
]
```
Note: correct_answer is the 0-indexed integer of the correct option. Ensure questions test genuine comprehension of this topic.

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
                            'maxOutputTokens' => 3000,
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

        // Extract quiz JSON before converting markdown to HTML
        $quiz = [];
        if (preg_match('/```(?:json)?\s*(\[\s*\{.*?\}\s*\])\s*```/is', $rawMarkdown, $qm)) {
            try {
                $parsed = json_decode($qm[1], true);
                if (is_array($parsed) && count($parsed) >= 2) {
                    $quiz = $parsed;
                    // Remove the raw quiz json block from the displayed lesson markdown
                    $rawMarkdown = str_replace($qm[0], '', $rawMarkdown);
                }
            } catch (\Throwable $e) {
                // Ignore parse errors, will fallback to synthesized quiz below
            }
        }

        // Clean markdown fences if any
        $cleanMarkdown = trim(preg_replace('/^```(?:markdown)?|```$/m', '', $rawMarkdown));
        // Remove empty Checkpoint Quiz heading if left over
        $cleanMarkdown = trim(preg_replace('/###?\s*Checkpoint Quiz\s*$/im', '', $cleanMarkdown));
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

        // Fallback to local quiz synthesis if AI didn't provide valid JSON
        if (empty($quiz)) {
            $quiz = $this->localSynthesizer->synthesizeQuiz($indicator, $indicator->student_title, $subjectName, $takeaways);
        }

        return [
            'markdown' => $cleanMarkdown,
            'html' => $html,
            'key_takeaways' => $takeaways,
            'quiz' => $quiz,
        ];
    }
}
