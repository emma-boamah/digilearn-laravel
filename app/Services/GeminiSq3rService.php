<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Sq3rAnalysis;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class GeminiSq3rService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Executes the full 5-stage sequential SQ3R pipeline for a document.
     */
    public function processDocument(Document $document): array
    {
        $analysis = Sq3rAnalysis::firstOrNew(['document_id' => $document->id]);
        
        // If already completed and has structured payload, return it immediately
        if ($analysis->exists && $analysis->status === 'completed' && !empty($analysis->structured_payload)) {
            return $analysis->structured_payload;
        }

        $analysis->file_path = $document->file_path;
        $analysis->status = 'processing';
        $analysis->save();

        try {
            $fullPath = storage_path('app/public/' . $document->file_path);
            $hasPdfFile = file_exists($fullPath) && strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)) === 'pdf';
            
            $fileBase64 = null;
            $extractedText = null;

            if ($hasPdfFile) {
                // If PDF is under 20MB, use inlineData binary payload for true multi-modal comprehension
                if (filesize($fullPath) < 20 * 1024 * 1024) {
                    $fileBase64 = base64_encode(file_get_contents($fullPath));
                }
                
                // Also get layout-aware text as fallback/context
                if (function_exists('exec')) {
                    $output = [];
                    $returnVar = 0;
                    $escaped = escapeshellarg($fullPath);
                    @exec("pdftotext -layout {$escaped} -", $output, $returnVar);
                    if ($returnVar === 0 && !empty($output)) {
                        $extractedText = implode("\n", $output);
                    }
                }
            }

            if (!$fileBase64 && !$extractedText) {
                $extractedText = $document->title;
            }

            // ==========================================
            // STAGE 1: SURVEY (Structural Mapping)
            // ==========================================
            $surveyPrompt = "Act as an academic engine executing SQ3R Step 1 (Survey). Read this document thoroughly. Build a complete structural map. Identify main modules, formal chapter titles, mathematical headers, or section dividers. Output a clean, scannable markdown outline mapping out the core technical themes.";
            $structuralMap = $this->queryGemini($surveyPrompt, null, $fileBase64, $extractedText);
            $analysis->structural_map = $structuralMap;
            $analysis->save();

            // ==========================================
            // STAGE 2: QUESTION (Goal Setting)
            // ==========================================
            $questionPrompt = "Act as an academic engine executing SQ3R Step 2 (Question). Look at the structural markdown map provided. Turn every single core heading, sub-header, and major formula complex into a highly specific analytical question. Output a concise markdown list of targeted learning objective goals.";
            $questionList = $this->queryGemini($questionPrompt, "Structural Map:\n" . $structuralMap, $fileBase64, $extractedText);
            $analysis->question_list = $questionList;
            $analysis->save();

            // ==========================================
            // STAGE 3: READ (Targeted Extraction)
            // ==========================================
            $readPrompt = "Act as an academic engine executing SQ3R Step 3 (Read). Isolate and resolve every question mapped out in our previous objectives list. Extract key rules, formulas, dense logic blocks, and formal definitions with standard LaTeX notation. Filter out decorative text fluff entirely. Return comprehensive technical raw notes.";
            $contentNotes = $this->queryGemini($readPrompt, "Question List:\n" . $questionList, $fileBase64, $extractedText);
            $analysis->content_notes = $contentNotes;
            $analysis->save();

            // ==========================================
            // STAGE 4: RECITE (Synthesis & Simplification)
            // ==========================================
            $recitePrompt = "Act as an academic engine executing SQ3R Step 4 (Recite). Take our extracted complex notes and translate them entirely into highly descriptive, simple, plain English summaries and intuitive analogies. Avoid heavy jargon where possible. Break down hard mathematical concepts into clean conceptual cards or brief fragments under 15 words per line.";
            $simpleSummary = $this->queryGemini($recitePrompt, "Technical Content Notes:\n" . $contentNotes, $fileBase64, $extractedText);
            $analysis->simple_summary = $simpleSummary;
            $analysis->save();

            // ==========================================
            // STAGE 5: REVIEW & FINAL STRUCTURE SYNTHESIS
            // ==========================================
            $reviewPrompt = "Act as an academic engine executing SQ3R Step 5 (Review). Compile all our processed steps into a pristine final Master Study Guide. Format an intuitive Q&A directory matching the questions to verified technical definitions. Build a scannable table mapping Rule Name, Math Formula (LaTeX), Simple Example, and Critical Pitfalls to Avoid.";
            $finalGuide = $this->queryGemini($reviewPrompt, "Simplified Summaries:\n" . $simpleSummary, $fileBase64, $extractedText);
            $analysis->final_guide = $finalGuide;

            // ==========================================
            // STAGE 6: COMPILE STRUCTURED UI PAYLOAD
            // ==========================================
            $payloadPrompt = <<<PROMPT
Using all the verified SQ3R analysis data below, compile the ultimate structured JSON for the student's interactive Acquisition Mode and Application Mode UI.

Document Title: {$document->title}

Survey Outline:
{$structuralMap}

Questions:
{$questionList}

Read Notes:
{$contentNotes}

Recite Summary:
{$simpleSummary}

Review Guide:
{$finalGuide}

Output MUST be valid JSON conforming to this schema (no markdown ticks):
{
  "docTitle": "{$document->title}",
  "vocabulary": [
    { "term": "Exact Concept Term", "def": "Clear, accessible definition." }
  ],
  "sections": [
    {
      "id": "sec-1",
      "title": "1. Exact Chapter/Topic Name",
      "paragraphs": [
        "First rich, clear explanatory paragraph.",
        "Second explanatory paragraph with intuitive analogy."
      ],
      "inquiry_focus": "The specific inquiry question formulated in Step 2.",
      "has_checkpoint": true, // true ONLY for testable rule/mechanism sections, false for intro/overview
      "checkpoint_prompt": "Active recall self-explanation prompt tailored specifically to this concept."
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
    "Milestone 1: Actionable step",
    "Milestone 2: Actionable step",
    "Milestone 3: Actionable step",
    "Milestone 4: Actionable step"
  ]
}
PROMPT;

            $jsonString = $this->queryGemini($payloadPrompt, null, null, null);
            $cleanJson = trim(preg_replace('/^```(?:json)?|```$/m', '', $jsonString));
            $payload = json_decode($cleanJson, true);

            if (!is_array($payload) || empty($payload['sections'])) {
                throw new Exception("SQ3R payload compilation returned invalid JSON schema.");
            }

            $analysis->structured_payload = $payload;
            $analysis->status = 'completed';
            $analysis->save();

            return $payload;

        } catch (Exception $e) {
            Log::warning('GeminiSq3rService multi-stage pipeline notice: ' . $e->getMessage() . ' - invoking Batch Grasping Cognitive Service.', [
                'doc_id' => $document->id
            ]);

            try {
                $cognitiveService = app(DocumentCognitiveService::class);
                $batchResult = $cognitiveService->analyzeDocumentContent($document, $extractedText);
                if (!empty($batchResult) && !empty($batchResult['sections'])) {
                    return $batchResult;
                }
            } catch (\Throwable $cogEx) {
                Log::warning('DocumentCognitiveService secondary fallback notice: ' . $cogEx->getMessage());
            }

            $analysis->status = 'failed';
            $analysis->error_message = $e->getMessage();
            $analysis->save();

            // Return clean dynamic fallback
            return $this->generateFallback($document);
        }
    }

    /**
     * Query Gemini with model fallbacks and multi-modal support.
     */
    protected function queryGemini(string $prompt, ?string $context = null, ?string $fileBase64 = null, ?string $rawText = null): string
    {
        if (empty($this->apiKey)) {
            throw new Exception("Gemini API key is not configured.");
        }

        $fullPrompt = $prompt;
        if ($context) {
            $fullPrompt = "### CONTEXT DATA FROM PREVIOUS SQ3R STEPS:\n" . $context . "\n\n### CURRENT TASK:\n" . $prompt;
        }

        $parts = [];
        
        // Add PDF binary if available
        if ($fileBase64) {
            $parts[] = [
                'inlineData' => [
                    'mimeType' => 'application/pdf',
                    'data' => $fileBase64
                ]
            ];
        } elseif ($rawText) {
            $parts[] = [
                'text' => "### SOURCE DOCUMENT TEXT:\n" . mb_substr($rawText, 0, 30000) . "\n\n"
            ];
        }

        $parts[] = [
            'text' => $fullPrompt
        ];

        $modelsToTry = [
            'gemini-flash-latest',
            'gemini-2.0-flash',
            'gemini-1.5-flash',
            'gemini-flash-lite-latest'
        ];

        if (!empty($this->model) && !in_array($this->model, $modelsToTry)) {
            array_unshift($modelsToTry, $this->model);
        }
        $modelsToTry = array_values(array_unique($modelsToTry));

        $lastError = null;
        foreach ($modelsToTry as $modelName) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$this->apiKey}";
            try {
                $response = Http::timeout(90)
                    ->withOptions([
                        'curl' => [
                            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
                        ]
                    ])
                    ->post($url, [
                        'contents' => [
                            ['role' => 'user', 'parts' => $parts]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.2,
                            'maxOutputTokens' => 4096,
                        ],
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    if (!empty(trim($text))) {
                        return trim($text);
                    }
                } else {
                    $lastError = "HTTP " . $response->status() . ": " . $response->body();
                }
            } catch (\Throwable $ex) {
                $lastError = $ex->getMessage();
            }
        }

        throw new Exception("All Gemini models failed. Last error: " . $lastError);
    }

    /**
     * Clean Fallback Structure
     */
    protected function generateFallback(Document $document): array
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
