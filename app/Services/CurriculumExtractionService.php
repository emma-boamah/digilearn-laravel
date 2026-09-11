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

Extract the complete hierarchical educational structure. Standard curriculum documents are organized into:
1. Strands (broad content domains / themes)
2. Sub-strands (specific topics within the strand)
3. Content Standards (what learners are expected to know and understand)
4. Indicators (specific, measurable learning objectives / outcomes, usually identified by codes like B7.1.1.1.1, B8.2.1.1, etc.)
5. Exemplars (concrete classroom activities, teaching examples, or problem scenarios)

If this curriculum covers multiple grades (e.g., Basic 7, Basic 8, Basic 9 OR JHS 1, JHS 2, JHS 3 OR Primary 1-6), make sure to indicate the grade_label for each strand or sub-strand.

Output MUST be strictly valid JSON without any markdown code fence backticks or introductory text:
{
  "curriculum_title": "Official Title of Curriculum",
  "education_body": "GES / NaCCA / WAEC / etc.",
  "subject": "{$curriculum->subject->name}",
  "strands": [
    {
      "title": "Strand 1: Name of Strand (e.g. Number)",
      "description": "Brief description of the strand scope.",
      "grade_label": "e.g. JHS 1 or Basic 7 or Primary 4 (or null if single-grade)",
      "sub_strands": [
        {
          "title": "Sub-strand 1: Topic Name (e.g. Number Operations)",
          "description": "Scope of this sub-strand.",
          "content_standard": "Statement of what learners should know/understand.",
          "indicators": [
            {
              "indicator_code": "e.g. B7.1.1.1.1",
              "title": "Short title of the indicator",
              "description": "Full description of what the learner will demonstrate or do.",
              "exemplars": "Concrete examples, sample tasks, or teacher guidelines mentioned in the curriculum."
            }
          ]
        }
      ]
    }
  ]
}
PROMPT;

            $rawResponse = $this->queryGemini($prompt, $fileBase64, $extractedText);
            
            // Clean markdown codeblocks if returned
            $cleanJson = trim(preg_replace('/^```(?:json)?|```$/m', '', $rawResponse));
            $data = json_decode($cleanJson, true);

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
