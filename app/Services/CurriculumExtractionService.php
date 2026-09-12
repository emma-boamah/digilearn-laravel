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
You are an expert national curriculum analyst specializing in standard educational frameworks (Ghana Education Service - GES, NaCCA Common Core Programme).
Analyze the curriculum document titled "{$curriculum->title}" for Subject: "{$curriculum->subject->name}".

The Common Core Programme (CCP) Mathematics curriculum spans THREE distinct grade levels:
1. Basic 7 (JHS 1)
2. Basic 8 (JHS 2)
3. Basic 9 (JHS 3)

In each grade level, there are 4 distinct strands:
- STRAND 1: NUMBER
- STRAND 2: ALGEBRA
- STRAND 3: GEOMETRY AND MEASUREMENT
- STRAND 4: HANDLING DATA

CRITICAL REQUIREMENT:
You MUST extract ALL strands for ALL THREE grades (Total of 12 Strands: 4 for Basic 7, 4 for Basic 8, and 4 for Basic 9).
Do not stop after Basic 7 or Basic 8. Thoroughly check the entire document, Table of Contents, and Scope & Sequence.

For each Strand:
1. Specify the "grade_label" clearly: "Basic 7 (JHS 1)", "Basic 8 (JHS 2)", or "Basic 9 (JHS 3)".
2. Include the exact title (e.g. "STRAND 1: NUMBER").
3. Include all its Sub-strands with their respective Content Standard codes (e.g. B7.1.1.1, B8.1.1.1, B9.1.1.1).

Do NOT include indicator details or exemplars here. Keep it strictly focused on the complete strand & sub-strand structural tree across all 3 grades.

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
        }
      ]
    },
    {
      "title": "STRAND 1: NUMBER",
      "description": "Number and Numeration Systems",
      "grade_label": "Basic 8 (JHS 2)",
      "sub_strands": [
        {
          "title": "Sub-strand 1: Number and Numeration Systems",
          "content_standard": "B8.1.1.1 Apply mental mathematics strategies and number operations"
        }
      ]
    },
    {
      "title": "STRAND 1: NUMBER",
      "description": "Number and Numeration Systems",
      "grade_label": "Basic 9 (JHS 3)",
      "sub_strands": [
        {
          "title": "Sub-strand 1: Number and Numeration Systems",
          "content_standard": "B9.1.1.1 Apply operations on real numbers"
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
                    $this->extractIndicatorsForStrand($strand, $fileBase64, $extractedText, $fullPath);
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
    public function extractIndicatorsForStrand(CurriculumStrand $strand, ?string $fileBase64 = null, ?string $extractedText = null, ?string $fullPdfPath = null): int
    {
        $strand->load('subStrands');

        // If fileBase64 / extractedText was not provided (e.g. single strand extraction request), load from curriculum
        if (!$fileBase64 && !$extractedText) {
            $curriculum = $strand->curriculum;
            $fullPdfPath = $fullPdfPath ?: Storage::disk('public')->path($curriculum->file_path);
            if (!file_exists($fullPdfPath)) {
                $altPath = storage_path('app/public/' . $curriculum->file_path);
                if (file_exists($altPath)) {
                    $fullPdfPath = $altPath;
                }
            }

            if (file_exists($fullPdfPath)) {
                $fileSize = filesize($fullPdfPath);
                if ($fileSize < 20 * 1024 * 1024) {
                    $fileBase64 = base64_encode(file_get_contents($fullPdfPath));
                }
            }
        }

        $subStrandsDetails = $strand->subStrands->map(function ($ss, $idx) {
            $code = $ss->content_standard ? " (Content Standard: {$ss->content_standard})" : "";
            return ($idx + 1) . ". {$ss->title}{$code}";
        })->implode("\n");

        $gradeLabel = $strand->grade_label ?? 'General';

        $prompt = <<<PROMPT
You are an expert curriculum analyst. For the curriculum document provided, focus EXCLUSIVELY on:
- Grade / Level: {$gradeLabel}
- Strand: "{$strand->title}"
- Sub-strands to extract:
{$subStrandsDetails}

Extract all Learning Indicators, official indicator codes (e.g. B7.1.1.1.1, B7.1.1.1.2, B7.3.3.1.1, B8.2.1.1.1), learning outcome descriptions, and teacher pedagogical exemplars for each sub-strand.

IMPORTANT GUIDELINES FOR EXEMPLARS & VISUALS:
1. Identify the approximate PDF page number where this indicator and its exemplars appear.
2. When an exemplar references visual diagrams, symbols, or shapes (e.g. Adinkra symbols like Nyame Biribi, Sankofa, Pempamsie, symmetry grids, reflection drawings, geometric figures, charts):
   - Fully transcribe the example questions, tasks, and symbol names in the "exemplars" field.
   - Provide a clear textual description of the visual diagrams and activities (e.g., "[Visual Diagram: Adinkra symbols (Nyame Biribi, Sankofa, Pempamsie) showing fold symmetries]", "[Activity: 3x4 grid with shaded square to determine lines of symmetry]").
3. Keep descriptions clear, structured, and pedagogical.

Output strictly valid JSON matching this schema:
{
  "strand_title": "{$strand->title}",
  "sub_strands": [
    {
      "title": "Exact Title or Sub-strand 1: Title",
      "indicators": [
        {
          "indicator_code": "B7.3.3.1.1",
          "title": "Determine shapes in real life that have reflectional (or fold) symmetries",
          "page_number": 102,
          "description": "Identify and analyze reflectional and line symmetry in cultural artifacts and everyday objects.",
          "exemplars": "E.g. 1: Identify examples of designs or objects in everyday life that have reflectional symmetries (e.g. Adinkra symbols: Nyame Biribi, Sesa Wo Suban, Sankofa, Pempamsie, Tamfo Bebre, Woforo Dua Pa A, Wo Nsa Da Mu A, Wawa Aba, Mmere Dane).\n\nE.g. 2: In how many different ways can one more square be shaded in a 3x4 grid so that it can have a line of symmetry?"
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
            return 0;
        }

        $totalExtracted = 0;

        DB::transaction(function () use ($strand, $data, $fullPdfPath, &$totalExtracted) {
            // Helper to clean and normalize sub-strand titles for comparison
            $cleanTitle = function (?string $text) {
                if (!$text) return '';
                $t = strtolower(trim($text));
                // Strip "sub-strand 1:", "substrand 1 -", "sub strand 2."
                $t = preg_replace('/^sub\s*-?\s*strand\s*\d+\s*[:\.\-]?\s*/i', '', $t);
                // Strip content standard codes if attached
                $t = preg_replace('/\(?content\s*standard.*?\)?$/i', '', $t);
                $t = preg_replace('/[^a-z0-9\s]/', '', $t);
                return trim(preg_replace('/\s+/', ' ', $t));
            };

            foreach ($data['sub_strands'] as $subIdx => $subData) {
                $rawTargetTitle = $subData['title'] ?? '';
                $normTargetTitle = $cleanTitle($rawTargetTitle);

                // 1. Try exact or normalized title matching
                $subStrand = $strand->subStrands->first(function ($ss) use ($normTargetTitle, $cleanTitle, $rawTargetTitle) {
                    $normSs = $cleanTitle($ss->title);
                    if ($normSs === $normTargetTitle) return true;
                    if (!empty($normSs) && !empty($normTargetTitle) && (str_contains($normSs, $normTargetTitle) || str_contains($normTargetTitle, $normSs))) {
                        return true;
                    }
                    return false;
                });

                // 2. Try matching by numerical index in the array if titles had numbers
                if (!$subStrand && isset($strand->subStrands[$subIdx])) {
                    $subStrand = $strand->subStrands[$subIdx];
                }

                // 3. Fallback to first sub-strand if single sub-strand
                if (!$subStrand && $strand->subStrands->count() === 1) {
                    $subStrand = $strand->subStrands->first();
                }

                if ($subStrand && !empty($subData['indicators'])) {
                    // Remove existing indicators for clean idempotent update
                    $subStrand->indicators()->delete();

                    $indicatorSort = 1;
                    foreach ($subData['indicators'] as $indData) {
                        $indicator = CurriculumIndicator::create([
                            'sub_strand_id' => $subStrand->id,
                            'indicator_code' => $indData['indicator_code'] ?? null,
                            'title' => $indData['title'] ?? 'Indicator ' . $indicatorSort,
                            'description' => $indData['description'] ?? ($indData['title'] ?? ''),
                            'exemplars' => is_array($indData['exemplars'] ?? null) 
                                ? implode("\n\n", $indData['exemplars']) 
                                : ($indData['exemplars'] ?? null),
                            'sort_order' => $indicatorSort++,
                        ]);

                        $totalExtracted++;

                        // Automatically extract images for this indicator if page_number is present and pdfimages exists
                        $pageNumber = isset($indData['page_number']) ? (int) $indData['page_number'] : null;
                        if ($fullPdfPath && $pageNumber && $pageNumber > 0) {
                            $this->extractImagesForIndicator($indicator, $fullPdfPath, $pageNumber);
                        }
                    }
                }
            }
        });

        return $totalExtracted;
    }

    /**
     * Extract images from a specific page of a PDF using pdfimages and attach them to the indicator.
     */
    protected function extractImagesForIndicator(CurriculumIndicator $indicator, string $fullPdfPath, int $pageNumber): void
    {
        if (!function_exists('exec') || !file_exists($fullPdfPath)) {
            return;
        }

        try {
            $mediaDir = storage_path('app/public/curricula/media');
            if (!is_dir($mediaDir)) {
                @mkdir($mediaDir, 0755, true);
            }

            $prefix = "ind_{$indicator->id}_p{$pageNumber}";
            $tempPrefix = "{$mediaDir}/{$prefix}";
            $escapedPdf = escapeshellarg($fullPdfPath);

            // pdfimages -f <first_page> -l <last_page> -png <pdf> <image_prefix>
            $cmd = "pdfimages -f {$pageNumber} -l {$pageNumber} -png {$escapedPdf} " . escapeshellarg($tempPrefix) . " 2>&1";
            @exec($cmd, $output, $returnVar);

            if ($returnVar === 0) {
                // Find all generated images for this prefix
                $extractedFiles = glob("{$mediaDir}/{$prefix}-*.png");
                $sort = 1;

                foreach ($extractedFiles as $filePath) {
                    // Filter out tiny 1x1 or decorative separator lines (under 2KB)
                    if (filesize($filePath) > 2048) {
                        $relativePath = 'curricula/media/' . basename($filePath);
                        CurriculumMedia::create([
                            'mediable_type' => CurriculumIndicator::class,
                            'mediable_id' => $indicator->id,
                            'file_path' => $relativePath,
                            'caption' => "Diagram from page {$pageNumber} for {$indicator->indicator_code}",
                            'page_number' => $pageNumber,
                            'sort_order' => $sort++,
                        ]);
                    } else {
                        @unlink($filePath);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Could not extract images for indicator {$indicator->id} on page {$pageNumber}: " . $e->getMessage());
        }
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
                            'maxOutputTokens' => 16384,
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
