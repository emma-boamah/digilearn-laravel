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
     * Process curriculum extraction from an uploaded PDF.
     */
    public function extractCurriculum(Curriculum $curriculum): array
    {
        $curriculum->update([
            'extraction_status' => 'processing',
            'extraction_error' => null,
        ]);

        try {
            $fullPath = Storage::disk('public')->path($curriculum->file_path);
            
            if (!file_exists($fullPath)) {
                // Fallback check in storage_path
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

            // Send as inline PDF if under 20MB
            if ($fileSize < 20 * 1024 * 1024) {
                $fileBase64 = base64_encode(file_get_contents($fullPath));
            }

            // Also attempt pdftotext extraction if available
            if (function_exists('exec')) {
                $output = [];
                $returnVar = 0;
                $escaped = escapeshellarg($fullPath);
                @exec("pdftotext -layout {$escaped} -", $output, $returnVar);
                if ($returnVar === 0 && !empty($output)) {
                    $extractedText = implode("\n", $output);
                }
            }

            $prompt = <<<PROMPT
You are an expert national curriculum analyst specializing in standard educational frameworks (including Ghana Education Service - GES, NaCCA, WAEC/WASSCE, and Cambridge).
Analyze the provided curriculum document titled "{$curriculum->title}" for Subject: "{$curriculum->subject->name}".

Look for the "Scope and Sequence", "Curriculum Structure", or the main tabular standards in this curriculum document.
Extract all Strands, Sub-strands, Content Standards, and Learning Indicators across all covered grades (e.g. Basic 7 / JHS 1, Basic 8 / JHS 2, Basic 9 / JHS 3).

Formatting requirements:
1. Ensure EVERY Strand and Sub-strand from the curriculum is captured.
2. For each Sub-strand, extract the Content Standard and its Indicators (with official codes like B7.1.1.1, B7.1.1.2, B8.1.1.1, etc.).
3. Keep descriptions and exemplars concise (1-2 sentences) so that all strands fit completely within the JSON response without hitting length limits.

Output MUST be strictly valid JSON matching this schema:
{
  "curriculum_title": "Official Title of Curriculum",
  "education_body": "GES / NaCCA / WAEC / etc.",
  "subject": "{$curriculum->subject->name}",
  "strands": [
    {
      "title": "STRAND 1: NUMBER",
      "description": "Number and Numeration Systems",
      "grade_label": "Basic 7 (JHS 1)",
      "sub_strands": [
        {
          "title": "Sub-strand 1: Number and Numeration Systems",
          "description": "Counting, representation, and operations",
          "content_standard": "B7.1.1.1 Demonstrate an understanding of place value of large numbers.",
          "indicators": [
            {
              "indicator_code": "B7.1.1.1.1",
              "title": "Model and represent numbers up to 1,000,000,000",
              "description": "Model number quantities and express numbers in standard form and place value chart.",
              "exemplars": "Use place value chart to write large numbers and identify values of digits."
            }
          ]
        }
      ]
    }
  ]
}
PROMPT;

            $rawResponse = $this->queryGemini($prompt, $fileBase64, $extractedText);
            
            $data = $this->parseAndRepairJson($rawResponse);

            if (!is_array($data) || empty($data['strands'])) {
                throw new Exception("Curriculum extraction did not return valid strands. Response preview: " . substr($rawResponse, 0, 300));
            }

            // Save to database inside transaction
            DB::transaction(function () use ($curriculum, $data) {
                // Delete any existing strands for fresh re-extraction
                $curriculum->strands()->delete();

                $strandSort = 1;
                foreach ($data['strands'] as $strandData) {
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
                        $subStrand = CurriculumSubStrand::create([
                            'strand_id' => $strand->id,
                            'title' => $subStrandData['title'],
                            'description' => $subStrandData['description'] ?? null,
                            'content_standard' => $subStrandData['content_standard'] ?? null,
                            'sort_order' => $subStrandSort++,
                        ]);

                        $indicatorSort = 1;
                        foreach ($subStrandData['indicators'] ?? [] as $indData) {
                            CurriculumIndicator::create([
                                'sub_strand_id' => $subStrand->id,
                                'indicator_code' => $indData['indicator_code'] ?? null,
                                'title' => $indData['title'],
                                'description' => $indData['description'] ?? $indData['title'],
                                'exemplars' => is_array($indData['exemplars'] ?? null) 
                                    ? implode("\n\n", $indData['exemplars']) 
                                    : ($indData['exemplars'] ?? null),
                                'sort_order' => $indicatorSort++,
                            ]);
                        }
                    }
                }

                $curriculum->update([
                    'extraction_status' => 'extracted',
                    'raw_extraction_json' => $data,
                    'extraction_error' => null,
                ]);
            });

            Log::info("Curriculum extraction completed successfully for ID {$curriculum->id}");
            return $data;

        } catch (\Throwable $e) {
            Log::error("Curriculum extraction failed for ID {$curriculum->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $curriculum->update([
                'extraction_status' => 'failed',
                'extraction_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Parse and repair JSON that may be truncated or wrapped in markdown.
     */
    protected function parseAndRepairJson(string $rawResponse): ?array
    {
        // 1. Strip markdown code fences if present
        $clean = trim(preg_replace('/^```(?:json)?|```$/m', '', trim($rawResponse)));

        // 2. Try direct decode first
        $decoded = json_decode($clean, true);
        if (is_array($decoded) && !empty($decoded['strands'])) {
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
                    if (is_array($repaired) && !empty($repaired['strands'])) {
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
