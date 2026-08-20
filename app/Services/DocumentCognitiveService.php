<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class DocumentCognitiveService
{
    private string $geminiApiKey;
    private string $geminiModel;

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));
        $this->geminiModel = config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Synthesize structured cognitive framework from document contents using Gemini AI.
     */
    public function analyzeDocumentContent(Document $document, ?string $extractedText = null, array $slides = []): array
    {
        $cacheKey = "doc_cognitive_v2_{$document->id}";

        // Return cached analysis if available
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            if (!empty($cached) && !empty($cached['sections'])) {
                return $cached;
            }
        }

        // Build source text
        $sourceText = $this->resolveSourceText($document, $extractedText, $slides);

        if (empty(trim($sourceText))) {
            return $this->generateFallbackStructure($document);
        }

        try {
            $aiStructure = $this->callGeminiForSynthesis($document->title, $sourceText);
            if (!empty($aiStructure) && !empty($aiStructure['sections'])) {
                // Cache for 7 days
                Cache::put($cacheKey, $aiStructure, 60 * 60 * 24 * 7);
                return $aiStructure;
            }
        } catch (\Throwable $e) {
            Log::warning('DocumentCognitiveService: Gemini analysis failed, using dynamic local fallback', [
                'doc_id' => $document->id,
                'error' => $e->getMessage()
            ]);
        }

        return $this->generateFallbackStructure($document, $sourceText);
    }

    /**
     * Resolve document text from client payload, PDF parsing, or slide content.
     */
    private function resolveSourceText(Document $document, ?string $extractedText, array $slides): string
    {
        if (!empty($extractedText) && strlen(trim($extractedText)) > 50) {
            return $extractedText;
        }

        if (!empty($slides)) {
            $slideTexts = [];
            foreach ($slides as $idx => $s) {
                $title = $s['title'] ?? ('Slide ' . ($idx + 1));
                $content = is_array($s['bullets'] ?? null) ? implode("\n", $s['bullets']) : ($s['content'] ?? '');
                $slideTexts[] = "### {$title}\n{$content}";
            }
            return implode("\n\n", $slideTexts);
        }

        // Try extracting text from storage if PDF
        $fullPath = storage_path('app/public/' . $document->file_path);
        if (file_exists($fullPath)) {
            if (function_exists('exec')) {
                $output = [];
                $returnVar = 0;
                $escapedPath = escapeshellarg($fullPath);
                @exec("pdftotext {$escapedPath} -", $output, $returnVar);
                if ($returnVar === 0 && !empty($output)) {
                    return implode("\n", $output);
                }
            }
        }

        return $document->title;
    }

    /**
     * Call Gemini API with multiple model fallbacks.
     */
    private function callGeminiForSynthesis(string $title, string $sourceText): array
    {
        if (empty($this->geminiApiKey)) {
            throw new Exception('Gemini API key is not configured.');
        }

        $truncatedText = mb_substr($sourceText, 0, 15000);

        $systemPrompt = <<<PROMPT
You are a senior cognitive education scientist and curriculum expert.
Your job is to read the source material from an academic document and transform it into a structured, pedagogically sound cognitive reading breakdown (SQ3R + Just-In-Time Learning).

Key Rules:
1. True Semantic Chapters:
   - Identify the real, logically ordered chapters/sections (e.g. 3 to 7 sections based on the actual material).
   - NEVER create fake chapters from introductory sentences or examples (e.g. "So, suppose we have", "Key Point", "Example 1").
   - NEVER truncate titles or formulas (e.g. use "Rule 5: Negative Powers (a⁻ⁿ = 1/aⁿ)", NOT "The fifth rule: a - 1 =").
2. Selective Active Recall Checkpoints:
   - "has_checkpoint": Set to true ONLY if this section contains a core testable rule, theorem, or mechanism. Set to false for introductory overviews or summary notes.
   - "checkpoint_prompt": If has_checkpoint is true, craft a targeted question testing the specific concept of that section. If false, set to null.
3. Math Formatting:
   - Use standard LaTeX notation (e.g. \\( a^m \\times a^n = a^{m+n} \\), \\( \\frac{a^m}{a^n} = a^{m-n} \\), \\( a^0 = 1 \\)).
4. Pure JSON Output:
   - Output MUST be valid JSON only. Do not include markdown code block ticks (` ```json `).
PROMPT;

        $userPrompt = <<<PROMPT
Document Title: {$title}

Source Content:
---
{$truncatedText}
---

Generate the JSON conforming exactly to this structure:
{
  "docTitle": "{$title}",
  "vocabulary": [
    { "term": "Term Name", "def": "Clear definition in 1-2 sentences." }
  ],
  "sections": [
    {
      "id": "sec-1",
      "title": "1. Section Title",
      "paragraphs": [
        "First explanatory paragraph explaining the concept in detail.",
        "Second explanatory paragraph with clear context or examples."
      ],
      "inquiry_focus": "Specific inquiry question focused on this chapter.",
      "has_checkpoint": false,
      "checkpoint_prompt": null
    },
    {
      "id": "sec-2",
      "title": "2. Next Concept or Rule",
      "paragraphs": [
        "Detailed explanation of this rule or concept."
      ],
      "inquiry_focus": "Why does this rule work and how is it used?",
      "has_checkpoint": true,
      "checkpoint_prompt": "Active Recall Checkpoint: Explain how you would apply this specific rule to solve an example."
    }
  ],
  "tech_rules": {
    "formula": "// Core Formula Matrix\\n1. Rule 1: Equation;\\n2. Rule 2: Equation;",
    "code": "function executeRule(x) {\\n    return x;\\n}",
    "note": "Important edge case consideration when applying these principles."
  },
  "checklist": [
    "Milestone 1 description",
    "Milestone 2 description",
    "Milestone 3 description",
    "Milestone 4 description"
  ]
}
PROMPT;

        $modelsToTry = [
            'gemini-flash-latest',
            'gemini-2.0-flash',
            'gemini-1.5-flash',
            'gemini-flash-lite-latest'
        ];

        if (!empty($this->geminiModel) && !in_array($this->geminiModel, $modelsToTry)) {
            array_unshift($modelsToTry, $this->geminiModel);
        }
        $modelsToTry = array_values(array_unique($modelsToTry));

        foreach ($modelsToTry as $modelName) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$this->geminiApiKey}";
            try {
                $response = Http::timeout(45)->post($url, [
                    'systemInstruction' => [
                        'parts' => [['text' => $systemPrompt]]
                    ],
                    'contents' => [
                        ['role' => 'user', 'parts' => [['text' => $userPrompt]]]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 4096,
                        'responseMimeType' => 'application/json'
                    ],
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $cleanJson = trim(preg_replace('/^```(?:json)?|```$/m', '', $rawText));
                    $decoded = json_decode($cleanJson, true);

                    if (is_array($decoded) && !empty($decoded['sections'])) {
                        return $decoded;
                    }
                }
            } catch (\Throwable $ex) {
                Log::warning("Gemini model {$modelName} failed during cognitive synthesis: " . $ex->getMessage());
            }
        }

        throw new Exception('All Gemini models failed to produce structured cognitive output.');
    }

    /**
     * Intelligent local fallback structure if AI is unavailable.
     */
    private function generateFallbackStructure(Document $document, string $sourceText = ''): array
    {
        $title = $document->title;
        return [
            'docTitle' => $title,
            'vocabulary' => [
                ['term' => 'Core Axiom', 'def' => "The primary foundational premise or governing law established throughout {$title}."],
                ['term' => 'Transformation Rule', 'def' => "Systematic procedural method used to manipulate variables and solve operations."],
                ['term' => 'Boundary Condition', 'def' => "The constraints and parameters defining where specific operations remain valid."],
                ['term' => 'Applied Model', 'def' => "Structured analytical framework connecting theoretical principles to concrete problems."]
            ],
            'sections' => [
                [
                    'id' => 'sec-1',
                    'title' => "1. Introduction to {$title}",
                    'paragraphs' => [
                        "A foundational understanding of {$title} is essential for mastering key analytical and mathematical processes.",
                        "Study the relationships and governing properties introduced in this module to build core conceptual mastery."
                    ],
                    'inquiry_focus' => "What core concepts and learning objectives are established in this overview?",
                    'has_checkpoint' => false,
                    'checkpoint_prompt' => null
                ],
                [
                    'id' => 'sec-2',
                    'title' => "2. Core Principles & Governing Rules",
                    'paragraphs' => [
                        "This section introduces the fundamental rules, transformation mechanisms, and computational techniques.",
                        "Ensure you understand the boundary conditions and step-by-step logic applied during problem solving."
                    ],
                    'inquiry_focus' => "Why do these governing rules work and how do they simplify complex operations?",
                    'has_checkpoint' => true,
                    'checkpoint_prompt' => "Active Recall Checkpoint: In your own words, explain the core rule and how to apply it to simplify an example."
                ],
                [
                    'id' => 'sec-3',
                    'title' => "3. Synthesis & Practical Applications",
                    'paragraphs' => [
                        "Synthesizing multiple rules allows us to solve advanced multi-step problems and real-world scenarios.",
                        "Practice combining these principles systematically to reinforce deep understanding."
                    ],
                    'inquiry_focus' => "How do we combine multiple rules to solve complex problem scenarios?",
                    'has_checkpoint' => true,
                    'checkpoint_prompt' => "Active Recall Checkpoint: Describe the step-by-step strategy you use to solve a multi-step problem in this topic."
                ]
            ],
            'tech_rules' => [
                'formula' => "// Structural Rules for {$title}\n1. Baseline validation\n2. Parameter constraints\n3. Functional invariance",
                'code' => "function process_{$document->id}(inputs) {\n    return inputs.map(i => i * 1.0);\n}",
                'note' => "Handle edge cases and parameter limits carefully when applying {$title} computational rules."
            ],
            'checklist' => [
                "Deconstruct problem specifications and core axioms in {$title}",
                "Apply computational rules to simplify baseline cases",
                "Verify boundary constraints and limit behaviors",
                "Compile final solution into the blueprint specification"
            ]
        ];
    }
}
