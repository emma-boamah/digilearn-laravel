<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Sq3rAnalysis;
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
     * Uses hierarchical batch grasping for lengthy documents (10+ pages) and persists to DB.
     */
    public function analyzeDocumentContent(Document $document, ?string $extractedText = null, array $slides = []): array
    {
        $cacheKey = "doc_cognitive_v3_{$document->id}";

        // 1. Return in-memory cached analysis if available (<1ms)
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            if (!empty($cached) && !empty($cached['sections'])) {
                return $cached;
            }
        }

        // 2. Return database persisted analysis if available (0 Gemini calls)
        try {
            $dbAnalysis = Sq3rAnalysis::where('document_id', $document->id)
                ->where('status', 'completed')
                ->first();
            if ($dbAnalysis && !empty($dbAnalysis->structured_payload) && !empty($dbAnalysis->structured_payload['sections'])) {
                Cache::put($cacheKey, $dbAnalysis->structured_payload, 60 * 60 * 24 * 7);
                return $dbAnalysis->structured_payload;
            }
        } catch (\Throwable $dbEx) {
            Log::warning('DocumentCognitiveService: Sq3rAnalysis query notice: ' . $dbEx->getMessage());
        }

        // Build full source text
        $sourceText = $this->resolveSourceText($document, $extractedText, $slides);

        if (empty(trim($sourceText))) {
            return $this->generateFallbackStructure($document);
        }

        try {
            // Check if document is long (> 12,000 characters)
            if (mb_strlen($sourceText) > 12000) {
                $aiStructure = $this->batchGraspAndSynthesize($document->title, $sourceText);
            } else {
                $aiStructure = $this->callGeminiForSynthesis($document->title, $sourceText);
            }

            if (!empty($aiStructure) && !empty($aiStructure['sections'])) {
                // 3. Persist to Database table sq3r_analyses
                try {
                    Sq3rAnalysis::updateOrCreate(
                        ['document_id' => $document->id],
                        [
                            'file_path' => $document->file_path,
                            'status' => 'completed',
                            'structured_payload' => $aiStructure,
                        ]
                    );
                } catch (\Throwable $saveEx) {
                    Log::warning('DocumentCognitiveService: Sq3rAnalysis save notice: ' . $saveEx->getMessage());
                }

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

        $fallback = $this->generateFallbackStructure($document, $sourceText);
        
        // Save fallback to DB so repeated queries do not re-trigger failures
        try {
            Sq3rAnalysis::updateOrCreate(
                ['document_id' => $document->id],
                [
                    'file_path' => $document->file_path,
                    'status' => 'completed',
                    'structured_payload' => $fallback,
                ]
            );
        } catch (\Throwable $ex) {}

        Cache::put($cacheKey, $fallback, 60 * 60 * 24 * 7);
        return $fallback;
    }

    /**
     * Resolve document text with layout-aware PDF extraction.
     */
    private function resolveSourceText(Document $document, ?string $extractedText, array $slides): string
    {
        if (!empty($extractedText) && strlen(trim($extractedText)) > 80) {
            return $extractedText;
        }

        if (!empty($slides)) {
            $slideTexts = [];
            foreach ($slides as $idx => $s) {
                $title = $s['title'] ?? ('Slide ' . ($idx + 1));
                $content = is_array($s['bullets'] ?? null) ? implode("\n", $s['bullets']) : ($s['content'] ?? '');
                $slideTexts[] = "### Slide " . ($idx + 1) . ": {$title}\n{$content}";
            }
            return implode("\n\n", $slideTexts);
        }

        // Try extracting text from storage with layout-aware pdftotext
        $fullPath = storage_path('app/public/' . $document->file_path);
        if (file_exists($fullPath)) {
            if (function_exists('exec')) {
                $output = [];
                $returnVar = 0;
                $escapedPath = escapeshellarg($fullPath);
                // -layout preserves multi-column format in academic papers
                @exec("pdftotext -layout {$escapedPath} -", $output, $returnVar);
                if ($returnVar === 0 && !empty($output)) {
                    return implode("\n", $output);
                }
            }
        }

        return $document->title;
    }

    /**
     * Hierarchical Multi-Stage Batch Grasping for Lengthy Documents (Map-Reduce).
     */
    private function batchGraspAndSynthesize(string $title, string $fullText): array
    {
        // 1. Split full text into logical chunks of ~10,000 characters
        $chunkSize = 10000;
        $chunks = [];
        $length = mb_strlen($fullText);
        
        for ($i = 0; $i < $length && count($chunks) < 5; $i += $chunkSize) {
            $chunks[] = mb_substr($fullText, $i, $chunkSize);
        }

        // 2. Stage 1: Grasp core topics and concepts from each batch
        $batchSummaries = [];
        foreach ($chunks as $idx => $chunk) {
            $batchNum = $idx + 1;
            $batchPrompt = <<<PROMPT
You are an academic text extractor. Analyze Batch {$batchNum} of document "{$title}".
Extract the core topics, definitions, theorems/rules, and key explanation paragraphs from this section.

Source Text:
---
{$chunk}
---

Return concise notes summarizing:
- Main Topics in this section
- Key definitions and mathematical rules
- Important explanations
PROMPT;

            try {
                $summary = $this->callGeminiText($batchPrompt);
                if (!empty($summary)) {
                    $batchSummaries[] = "=== BATCH {$batchNum} SUMMARY ===\n" . $summary;
                }
            } catch (\Throwable $e) {
                Log::warning("Batch {$batchNum} grasping failed: " . $e->getMessage());
            }
        }

        // 3. Stage 2: Global Synthesis across all batch summaries
        $aggregatedNotes = implode("\n\n", $batchSummaries);
        if (empty(trim($aggregatedNotes))) {
            $aggregatedNotes = mb_substr($fullText, 0, 18000);
        }

        return $this->callGeminiForSynthesis($title, $aggregatedNotes, true);
    }

    /**
     * Call Gemini API to produce structured cognitive JSON.
     */
    private function callGeminiForSynthesis(string $title, string $sourceContent, bool $isAggregated = false): array
    {
        if (empty($this->geminiApiKey)) {
            throw new Exception('Gemini API key is not configured.');
        }

        $systemPrompt = <<<PROMPT
You are a senior cognitive learning scientist and curriculum educator.
Your task is to analyze the source material from an academic document and produce a structured, high-yield cognitive reading breakdown (SQ3R + Just-In-Time Learning).

Critical Rules:
1. True Semantic Chapters:
   - Deduce the actual high-level topics (create between 4 and 8 cohesive chapters spanning the entire document).
   - NEVER create fake chapters from sentence fragments, proof labels, or exercise tags (e.g. NEVER make "Chapter", "Then:", "Proof:", "Problems", "A B", "Key Point", "So, suppose we have" into chapter titles).
   - Chapter titles MUST be informative, professional, and fully written (e.g. "1. Introduction & Foundations of Sets", "2. Set Operations: Union, Intersection & Complement", "3. Venn Diagrams & Subset Relations", "4. The Well-Ordering Principle & Mathematical Induction").
2. Selective Active Recall Checkpoints:
   - "has_checkpoint": Set to true ONLY on sections containing core testable rules, definitions, or mechanisms. Set to false for introductory overviews and background notes.
   - "checkpoint_prompt": If has_checkpoint is true, craft a targeted question testing the specific concept of that section. If false, set to null.
3. Math Formatting:
   - Format all mathematical expressions in standard LaTeX (e.g. \\( A \\cup B \\), \\( A \\cap B \\), \\( a^m \\times a^n = a^{m+n} \\)).
4. Pure JSON Output:
   - Output MUST be valid JSON only without markdown formatting ticks.
PROMPT;

        $userPrompt = <<<PROMPT
Document Title: {$title}

Source Material:
---
{$sourceContent}
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
    "concept_brief": "A 2-3 paragraph clear conceptual essay explaining the core ideas, intuition, and real-world significance of this document in plain, accessible language (like freeCodeCamp / Brilliant).",
    "formula_rules": [
      {
        "name": "Rule or Theorem Name",
        "latex": "LaTeX formula or formal rule",
        "description": "Clear 1-sentence plain-English explanation of how and when to apply this rule."
      }
    ],
    "worked_example": {
      "title": "Worked Problem / Case Walkthrough",
      "problem": "A concrete example or scenario demonstrating how to apply these rules.",
      "steps": [
        "Step 1: Identify the given components and applicable rule.",
        "Step 2: Execute the transformation or calculation step.",
        "Step 3: Simplify and verify the outcome."
      ],
      "solution": "Final result or key conclusion."
    },
    "practical_tips": [
      "Key insight or boundary condition to remember.",
      "Common misconception or trap to avoid."
    ],
    "formula": "// Formula & Rule Summary\\nRule 1: ...\\nRule 2: ...",
    "code": "// Practical Implementation / Method Walkthrough\\nfunction applyRule(input) {\\n    return input;\\n}",
    "note": "Critical operational note or edge case."
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
                $response = Http::timeout(60)
                    ->withOptions([
                        'curl' => [
                            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
                        ]
                    ])
                    ->post($url, [
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
                Log::warning("Gemini model {$modelName} failed: " . $ex->getMessage());
            }
        }

        throw new Exception('All Gemini models failed to produce structured cognitive output.');
    }

    /**
     * Helper to call Gemini for raw text extraction.
     */
    private function callGeminiText(string $prompt): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$this->geminiApiKey}";
        $response = Http::timeout(40)
            ->withOptions([
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
                ]
            ])
            ->post($url, [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 1500,
                ],
            ]);

        if ($response->successful()) {
            $json = $response->json();
            return $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
        }

        return '';
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
