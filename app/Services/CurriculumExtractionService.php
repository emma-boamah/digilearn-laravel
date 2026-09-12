<?php

namespace App\Services;

use App\Models\Curriculum;
use App\Models\CurriculumStrand;
use App\Models\CurriculumSubStrand;
use App\Models\CurriculumIndicator;
use App\Models\CurriculumMedia;
use App\Models\Level;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class CurriculumExtractionService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));
        $this->model = config('services.gemini.model', 'gemini-flash-latest');
    }

    /**
     * Process curriculum extraction from an uploaded PDF using a Two-Stage Pipeline.
     * Stage 1: Extracts the complete structural tree (ALL strands and sub-strands across Basic 7, 8, 9).
     * Stage 2: In-depth extraction of learning indicators and teacher exemplars for each strand.
     */
    public function extractCurriculum(Curriculum $curriculum): array
    {
        $curriculum->update([
            'extraction_status' => 'processing',
            'extraction_error' => null,
            'notes' => 'Stage 1: Analyzing curriculum structure and extracting all strands across grades...',
        ]);

        try {
            $fullPath = Storage::disk('public')->path($curriculum->file_path);
            
            if (!file_exists($fullPath)) {
                $altPath = storage_path('app/public/' . $curriculum->file_path);
                if (file_exists($altPath)) {
                    $fullPath = $altPath;
                } else {
                    throw new Exception("Curriculum PDF file not found at path: {$curriculum->file_path}");
                }
            }

            $fileSize = filesize($fullPath);
            $curriculum->update(['file_size_bytes' => $fileSize]);

            $fileBase64 = null;
            $extractedText = null;

            if ($fileSize < 20 * 1024 * 1024) {
                $fileBase64 = base64_encode(file_get_contents($fullPath));
            }

            if (function_exists('exec')) {
                $output = [];
                $returnVar = 0;
                $escaped = escapeshellarg($fullPath);
                @exec("pdftotext -layout {$escaped} -", $output, $returnVar);
                if ($returnVar === 0 && !empty($output)) {
                    $extractedText = implode("\n", $output);
                }
            }

            // ==========================================
            // STAGE 1: Extract 100% of Strands & Sub-strands
            // ==========================================
            Log::info("Curriculum ID {$curriculum->id}: Starting Stage 1 (Complete Structural Skeleton Extraction)");

            $structurePrompt = <<<PROMPT
You are an expert national curriculum analyst specializing in standard educational frameworks (including Ghana Education Service - GES, NaCCA, WAEC/WASSCE, and Cambridge).
Analyze the provided curriculum document titled "{$curriculum->title}" for Subject: "{$curriculum->subject->name}".

Extract the COMPLETE curriculum structure across ALL grades covered in this document (e.g., Basic 7 / JHS 1, Basic 8 / JHS 2, Basic 9 / JHS 3).
Look closely at the Table of Contents, Scope and Sequence, and curriculum overview tables.

Requirements:
1. Extract EVERY Strand for every grade level (e.g. Basic 7 Strand 1, Strand 2, Strand 3, Strand 4; Basic 8 Strand 1, 2, 3, 4; Basic 9 Strand 1, 2, 3, 4).
2. For each Strand, list all its Sub-strands and their Content Standard codes/titles (e.g. B7.1.1.1, B7.2.1.1).
3. Do NOT include lengthy indicator descriptions or exemplars in this step. Keep it focused on the complete hierarchy.

Output strictly valid JSON matching this schema:
{
  "curriculum_title": "{$curriculum->title}",
  "education_body": "GES / NaCCA",
  "subject": "{$curriculum->subject->name}",
  "strands": [
    {
      "title": "STRAND 1: NUMBER",
      "description": "Number and Numeration Systems",
      "grade_label": "Basic 7 (JHS 1)",
      "sub_strands": [
        {
          "title": "Sub-strand 1: Number and Numeration Systems",
          "content_standard": "B7.1.1.1 Demonstrate an understanding of place value of large numbers"
        },
        {
          "title": "Sub-strand 2: Number Operations",
          "content_standard": "B7.1.2.1 Apply mental mathematics strategies and number operations"
        }
      ]
    },
    {
      "title": "STRAND 2: ALGEBRA",
      "description": "Patterns, Relations, and Algebraic Expressions",
      "grade_label": "Basic 7 (JHS 1)",
      "sub_strands": [
        {
          "title": "Sub-strand 1: Patterns and Relations",
          "content_standard": "B7.2.1.1 Extend and apply patterns and relations"
        }
      ]
    }
  ]
}
PROMPT;

            $structureRaw = $this->queryGemini($structurePrompt, $fileBase64, $extractedText);
            $structureData = $this->parseAndRepairJson($structureRaw, 'strands');

            if (!is_array($structureData) || empty($structureData['strands'])) {
                throw new Exception("Stage 1 failed: Could not extract curriculum structure. Raw preview: " . substr($structureRaw, 0, 300));
            }

            // Save Strands & Sub-strands inside transaction
            $savedStrands = [];
            DB::transaction(function () use ($curriculum, $structureData, &$savedStrands) {
                $curriculum->strands()->delete();

                $strandSort = 1;
                foreach ($structureData['strands'] as $strandData) {
                    $gradeLabel = $strandData['grade_label'] ?? null;
                    $levelId = $this->resolveLevelId($gradeLabel, $curriculum->level_id);

                    $strand = CurriculumStrand::create([
                        'curriculum_id' => $curriculum->id,
                        'title' => $strandData['title'],
                        'description' => $strandData['description'] ?? null,
                        'sort_order' => $strandSort++,
                        'grade_label' => $gradeLabel,
                        'level_id' => $levelId,
                    ]);

                    $subStrandSort = 1;
                    foreach ($strandData['sub_strands'] ?? [] as $subStrandData) {
                        CurriculumSubStrand::create([
                            'strand_id' => $strand->id,
                            'title' => $subStrandData['title'],
                            'description' => $subStrandData['description'] ?? null,
                            'content_standard' => $subStrandData['content_standard'] ?? null,
                            'sort_order' => $subStrandSort++,
                        ]);
                    }

                    $savedStrands[] = $strand;
                }
            });

            $totalStrands = count($savedStrands);
            Log::info("Curriculum ID {$curriculum->id}: Stage 1 complete! Saved {$totalStrands} strands across grades.");

            // ==========================================
            // STAGE 2: Deep Indicator Extraction per Strand
            // ==========================================
            $processedCount = 0;
            foreach ($savedStrands as $index => $strand) {
                $strandNumber = $index + 1;
                $grade = $strand->grade_label ? " ({$strand->grade_label})" : "";
                
                $curriculum->update([
                    'notes' => "Stage 2: Extracting indicators for Strand {$strandNumber} of {$totalStrands}{$grade}: {$strand->title}...",
                ]);

                try {
                    $this->extractIndicatorsForStrand($strand, $fileBase64, $extractedText);
                } catch (\Throwable $ex) {
                    Log::warning("Stage 2 warning for Strand '{$strand->title}': " . $ex->getMessage());
                }

                $processedCount++;
            }

            $curriculum->update([
                'extraction_status' => 'extracted',
                'raw_extraction_json' => $structureData,
                'extraction_error' => null,
                'notes' => "Extraction successfully completed: {$totalStrands} strands with full learning indicators and exemplars.",
            ]);

            Log::info("Curriculum extraction completed successfully for ID {$curriculum->id} ({$totalStrands} strands).");
            return $structureData;

        } catch (\Throwable $e) {
            Log::error("Curriculum extraction failed for ID {$curriculum->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $curriculum->update([
                'extraction_status' => 'failed',
                'extraction_error' => $e->getMessage(),
                'notes' => 'Extraction failed: ' . $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Stage 2: Extract detailed indicators, codes, descriptions, and exemplars for a single strand.
     */
    public function extractIndicatorsForStrand(CurriculumStrand $strand, ?string $fileBase64, ?string $extractedText): void
    {
        $strand->load('subStrands');
        $subStrandsList = $strand->subStrands->pluck('title')->implode(', ');
        $gradeLabel = $strand->grade_label ?? 'General';

        $prompt = <<<PROMPT
You are an expert curriculum analyst. For the curriculum document provided, focus EXCLUSIVELY on:
- Grade / Level: {$gradeLabel}
- Strand: "{$strand->title}"
- Sub-strands: {$subStrandsList}

Extract all Learning Indicators, official indicator codes (e.g. B7.1.1.1.1, B7.1.1.1.2), learning outcome descriptions, and teacher pedagogical exemplars for this specific strand.

Output strictly valid JSON matching this schema:
{
  "strand_title": "{$strand->title}",
  "sub_strands": [
    {
      "title": "Exact Title of Sub-strand",
      "indicators": [
        {
          "indicator_code": "B7.1.1.1.1",
          "title": "Model and represent numbers up to 1,000,000,000",
          "description": "Model number quantities and express numbers in standard form and place value chart.",
          "exemplars": "Use place value charts to write numbers and determine digit values. Represent quantities using multi-base blocks."
        }
      ]
    }
  ]
}
PROMPT;

        $rawResponse = $this->queryGemini($prompt, $fileBase64, $extractedText);
        $data = $this->parseAndRepairJson($rawResponse, 'sub_strands');

        if (!is_array($data) || empty($data['sub_strands'])) {
            Log::warning("No indicators extracted for strand {$strand->id} ('{$strand->title}')");
            return;
        }

        DB::transaction(function () use ($strand, $data) {
            foreach ($data['sub_strands'] as $subData) {
                // Match sub-strand by title or fallback to existing
                $subStrand = $strand->subStrands->first(function ($ss) use ($subData) {
                    $t1 = strtolower(trim($ss->title));
                    $t2 = strtolower(trim($subData['title'] ?? ''));
                    return $t1 === $t2 || str_contains($t1, $t2) || str_contains($t2, $t1);
                });

                if (!$subStrand && $strand->subStrands->isNotEmpty()) {
                    $subStrand = $strand->subStrands->first();
                }

                if ($subStrand && !empty($subData['indicators'])) {
                    // Remove existing indicators for clean idempotent update
                    $subStrand->indicators()->delete();

                    $indicatorSort = 1;
                    foreach ($subData['indicators'] as $indData) {
                        CurriculumIndicator::create([
                            'sub_strand_id' => $subStrand->id,
                            'indicator_code' => $indData['indicator_code'] ?? null,
                            'title' => $indData['title'] ?? 'Indicator ' . $indicatorSort,
                            'description' => $indData['description'] ?? ($indData['title'] ?? ''),
                            'exemplars' => is_array($indData['exemplars'] ?? null) 
                                ? implode("\n\n", $indData['exemplars']) 
                                : ($indData['exemplars'] ?? null),
                            'sort_order' => $indicatorSort++,
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Parse and repair JSON that may be truncated or wrapped in markdown.
     */
    protected function parseAndRepairJson(string $rawResponse, string $expectedKey = 'strands'): ?array
    {
        // 1. Strip markdown code fences if present
        $clean = trim(preg_replace('/^```(?:json)?|```$/m', '', trim($rawResponse)));

        // 2. Try direct decode first
        $decoded = json_decode($clean, true);
        if (is_array($decoded) && (!empty($decoded[$expectedKey]) || isset($decoded[$expectedKey]))) {
            return $decoded;
        }

        // 3. Locate opening brace
        $firstBrace = strpos($clean, '{');
        if ($firstBrace === false) {
            return null;
        }

        $candidate = substr($clean, $firstBrace);

        // Step-by-step backtrack repair:
        // When JSON cuts off abruptly, it typically leaves a dangling string or partial key/value:
        // e.g. "description": "Number and Numerat
        // If we trim back to the last valid complete property separator (comma or curly/square bracket),
        // we can safely close the remaining structures!
        for ($i = strlen($candidate) - 1; $i > 0; $i--) {
            $char = $candidate[$i];
            
            // Look for clean cut points: after a completed object '}', array ']', or comma ','
            if ($char === '}' || $char === ']' || $char === ',') {
                $sub = substr($candidate, 0, $char === ',' ? $i : $i + 1);
                
                // Count unclosed brackets
                $openCurlies = substr_count($sub, '{') - substr_count($sub, '}');
                $openSquares = substr_count($sub, '[') - substr_count($sub, ']');
                
                if ($openCurlies >= 0 && $openSquares >= 0) {
                    $attempt = $sub;
                    for ($s = 0; $s < $openSquares; $s++) {
                        $attempt .= ']';
                    }
                    for ($c = 0; $c < $openCurlies; $c++) {
                        $attempt .= '}';
                    }

                    $repaired = json_decode($attempt, true);
                    if (is_array($repaired) && (!empty($repaired[$expectedKey]) || isset($repaired[$expectedKey]))) {
                        Log::info("CurriculumExtractionService: Successfully repaired truncated JSON at index {$i}.");
                        return $repaired;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Resolve level_id based on extracted grade_label string.
     */
    protected function resolveLevelId(?string $gradeLabel, ?int $defaultLevelId): ?int
    {
        if (empty($gradeLabel)) {
            return $defaultLevelId;
        }

        $clean = strtolower(trim($gradeLabel));

        // Match common Ghanaian grade indicators
        if (str_contains($clean, 'jhs 1') || str_contains($clean, 'basic 7') || str_contains($clean, 'b7')) {
            return Level::where('slug', 'jhs-1')->value('id') ?? $defaultLevelId;
        }
        if (str_contains($clean, 'jhs 2') || str_contains($clean, 'basic 8') || str_contains($clean, 'b8')) {
            return Level::where('slug', 'jhs-2')->value('id') ?? $defaultLevelId;
        }
        if (str_contains($clean, 'jhs 3') || str_contains($clean, 'basic 9') || str_contains($clean, 'b9')) {
            return Level::where('slug', 'jhs-3')->value('id') ?? $defaultLevelId;
        }
        if (str_contains($clean, 'shs 1')) {
            return Level::where('slug', 'shs-1')->value('id') ?? $defaultLevelId;
        }
        if (str_contains($clean, 'shs 2')) {
            return Level::where('slug', 'shs-2')->value('id') ?? $defaultLevelId;
        }
        if (str_contains($clean, 'shs 3')) {
            return Level::where('slug', 'shs-3')->value('id') ?? $defaultLevelId;
        }
        if (str_contains($clean, 'primary 1') || str_contains($clean, 'basic 1') || str_contains($clean, 'b1')) {
            return Level::where('slug', 'primary-1')->value('id') ?? $defaultLevelId;
        }

        return $defaultLevelId;
    }

    /**
     * Query Google Gemini API with fallback models.
     */
    protected function queryGemini(string $prompt, ?string $fileBase64 = null, ?string $rawText = null): string
    {
        if (empty($this->apiKey)) {
            throw new Exception("Gemini API key is not configured in services.gemini.key or GEMINI_API_KEY.");
        }

        $parts = [];

        if ($fileBase64) {
            $parts[] = [
                'inlineData' => [
                    'mimeType' => 'application/pdf',
                    'data' => $fileBase64,
                ]
            ];
        } elseif ($rawText) {
            $parts[] = [
                'text' => "### SOURCE DOCUMENT TEXT:\n" . mb_substr($rawText, 0, 45000) . "\n\n"
            ];
        }

        $parts[] = [
            'text' => $prompt
        ];

        $modelsToTry = [
            $this->model,
            'gemini-flash-latest',
            'gemini-2.0-flash',
            'gemini-1.5-flash',
            'gemini-flash-lite-latest',
        ];
        $modelsToTry = array_values(array_unique(array_filter($modelsToTry)));

        $lastError = null;

        foreach ($modelsToTry as $modelName) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$this->apiKey}";

            try {
                $response = Http::timeout(120)
                    ->withOptions([
                        'curl' => [
                            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                        ]
                    ])
                    ->post($url, [
                        'contents' => [
                            ['role' => 'user', 'parts' => $parts]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.1,
                            'maxOutputTokens' => 8192,
                            'responseMimeType' => 'application/json',
                        ],
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    if (!empty(trim($text))) {
                        return trim($text);
                    }
                } else {
                    $lastError = "Model {$modelName} failed HTTP " . $response->status() . ": " . $response->body();
                }
            } catch (\Throwable $ex) {
                $lastError = "Model {$modelName} exception: " . $ex->getMessage();
            }
        }

        throw new Exception("All Gemini models failed for curriculum extraction. " . $lastError);
    }
}
