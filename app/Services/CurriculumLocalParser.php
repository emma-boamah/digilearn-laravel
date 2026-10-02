<?php

namespace App\Services;

use App\Models\Curriculum;
use App\Models\CurriculumStrand;
use App\Models\CurriculumSubStrand;
use App\Models\CurriculumIndicator;
use App\Models\Level;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class CurriculumLocalParser
{
    public const VERSION = 'v1.0-deterministic';

    public function __construct(
        protected PdfTextExtractionService $pdfExtractor
    ) {}

    /**
     * Deterministically parse a curriculum PDF without external AI.
     */
    public function parse(Curriculum $curriculum): array
    {
        Log::info("CurriculumLocalParser: Starting deterministic parse for Curriculum ID {$curriculum->id}");

        $fullPath = Storage::disk('public')->path($curriculum->file_path);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/public/' . $curriculum->file_path);
        }

        if (!file_exists($fullPath)) {
            throw new Exception("Curriculum PDF not found at path: {$curriculum->file_path}");
        }

        // 1. Extract pages with PdfTextExtractionService
        $pages = $this->pdfExtractor->extractAllPages($fullPath);
        $pageCount = count($pages);
        $ocrNeeded = $this->pdfExtractor->detectOcrRequirement($pages);

        $curriculum->update([
            'source_page_count' => $pageCount,
            'ocr_required' => $ocrNeeded,
            'file_size_bytes' => filesize($fullPath),
        ]);

        if ($ocrNeeded) {
            $curriculum->update([
                'extraction_status' => 'failed',
                'extraction_error' => 'Document contains insufficient machine-readable text. OCR is required.',
                'extraction_method' => 'local',
                'requires_review' => true,
            ]);
            throw new Exception("Document requires OCR. Average text density is too low.");
        }

        // 2. Parse curriculum structure from text
        $parsedStructure = $this->extractStructureFromPages($pages, $curriculum);

        if (empty($parsedStructure['strands'])) {
            throw new Exception("Local parser could not identify standard curriculum strands or indicators in this document.");
        }

        // 3. Persist the extracted structure in database
        DB::transaction(function () use ($curriculum, $parsedStructure) {
            // Remove existing data if re-parsing
            $curriculum->strands()->delete();

            $strandSort = 1;
            foreach ($parsedStructure['strands'] as $sData) {
                $levelId = $this->resolveLevelId($sData['grade_label'] ?? '', $curriculum->level_id);

                $strand = CurriculumStrand::create([
                    'curriculum_id' => $curriculum->id,
                    'title' => $sData['title'],
                    'description' => $sData['description'] ?? null,
                    'sort_order' => $strandSort++,
                    'grade_label' => $sData['grade_label'] ?? null,
                    'level_id' => $levelId,
                ]);

                $subStrandSort = 1;
                foreach ($sData['sub_strands'] as $subData) {
                    $subStrand = CurriculumSubStrand::create([
                        'strand_id' => $strand->id,
                        'title' => $subData['title'],
                        'description' => $subData['description'] ?? null,
                        'content_standard' => $subData['content_standard'] ?? null,
                        'sort_order' => $subStrandSort++,
                    ]);

                    $indSort = 1;
                    foreach ($subData['indicators'] as $indData) {
                        CurriculumIndicator::create([
                            'sub_strand_id' => $subStrand->id,
                            'indicator_code' => $indData['indicator_code'] ?? null,
                            'title' => $indData['title'],
                            'description' => $indData['description'] ?? null,
                            'exemplars' => $indData['exemplars'] ?? null,
                            'sort_order' => $indSort++,
                        ]);
                    }
                }
            }

            $curriculum->update([
                'extraction_status' => 'extracted',
                'extraction_method' => 'local',
                'extraction_quality' => count($parsedStructure['strands']) >= 4 ? 'high' : 'medium',
                'requires_review' => true,
                'local_parser_version' => self::VERSION,
                'raw_extraction_json' => $parsedStructure,
                'extraction_error' => null,
            ]);
        });

        Log::info("CurriculumLocalParser: Completed successfully for Curriculum ID {$curriculum->id}. Saved " . count($parsedStructure['strands']) . " strands.");

        return $parsedStructure;
    }

    /**
     * Parse pages into hierarchical strands, sub-strands, and indicators.
     */
    public function extractStructureFromPages(array $pages, Curriculum $curriculum): array
    {
        $standardStrandNames = [
            1 => 'NUMBER',
            2 => 'ALGEBRA',
            3 => 'GEOMETRY AND MEASUREMENT',
            4 => 'HANDLING DATA',
        ];

        // Standard GES/NaCCA curriculum sub-strand definitions for Mathematics
        $standardMathSubStrands = [
            1 => [
                1 => 'Number and Numeration Systems',
                2 => 'Number Operations',
                3 => 'Fractions, Decimals and Percentages',
                4 => 'Ratios and Proportion',
            ],
            2 => [
                1 => 'Patterns and Relations',
                2 => 'Algebraic Expressions',
                3 => 'Variables and Equations',
            ],
            3 => [
                1 => 'Shapes and Space',
                2 => 'Measurement',
                3 => 'Position and Transformation',
            ],
            4 => [
                1 => 'Data',
                2 => 'Chance and Probability',
            ],
        ];

        // Track sub-strand titles discovered across pages by strand and sub-strand: [$strandNum][$subNum]
        $subStrandTitles = [];
        $activeStrandNum = 1;
        foreach ($pages as $p) {
            $pLines = explode("\n", $p['normalized_text']);
            foreach ($pLines as $l) {
                if (preg_match('/STRAND\s+(\d+)\s*[:.\-]?/i', $l, $sm)) {
                    $activeStrandNum = (int) $sm[1];
                }
                if (preg_match('/Sub[-\s]?strand\s+(\d+)\s*[:.\-]?\s*([A-Za-z].*?)$/i', trim($l), $m)) {
                    $sNum = (int) $m[1];
                    $sName = trim(preg_replace('/\.{2,}\s*\d+$/', '', $m[2]));
                    if (strlen($sName) > 2) {
                        $subStrandTitles[$activeStrandNum][$sNum] = $sName;
                    }
                }
            }
        }

        $tree = [];
        $currentCode = null;
        $currentIndicatorRef = null;

        foreach ($pages as $p) {
            $lines = explode("\n", $p['normalized_text']);

            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (empty($trimmed)) continue;

                // 1. Check for Indicator Code (e.g. B7.1.1.1.1 or B8.2.1.1.1)
                if (preg_match('/\b(B[1-9]\.(\d+)\.(\d+)\.\d+\.\d+)\s+([A-Za-z].*?)(?:\s{4,}|$)/', $line, $m)) {
                    $code = $m[1];
                    $title = trim($m[4]);

                    $gradeNum = (int) substr($code, 1, strpos($code, '.') - 1);
                    $gradeLabel = $gradeNum <= 6 
                        ? "Basic {$gradeNum}"
                        : "Basic {$gradeNum} (JHS " . ($gradeNum - 6) . ")";

                    $strandNum = (int) $m[2];
                    $subNum = (int) $m[3];

                    $strandKey = "{$gradeLabel}-{$strandNum}";
                    if (!isset($tree[$strandKey])) {
                        $sName = $standardStrandNames[$strandNum] ?? "STRAND {$strandNum}";
                        $tree[$strandKey] = [
                            'grade_label' => $gradeLabel,
                            'title' => "STRAND {$strandNum}: {$sName}",
                            'description' => "Curriculum strand covering {$sName}.",
                            'sub_strands' => [],
                        ];
                    }

                    $subKey = "sub-{$subNum}";
                    if (!isset($tree[$strandKey]['sub_strands'][$subKey])) {
                        $subName = $subStrandTitles[$strandNum][$subNum]
                            ?? $standardMathSubStrands[$strandNum][$subNum]
                            ?? "Sub-strand {$subNum}";
                        $tree[$strandKey]['sub_strands'][$subKey] = [
                            'title' => "SUB-STRAND {$subNum}: " . mb_strtoupper($subName),
                            'content_standard' => null,
                            'indicators' => [],
                        ];
                    }

                    $tree[$strandKey]['sub_strands'][$subKey]['indicators'][$code] = [
                        'indicator_code' => $code,
                        'title' => $title,
                        'description' => "Demonstrate understanding of {$title}",
                        'exemplars' => '',
                    ];

                    $currentCode = $code;
                    $currentIndicatorRef = &$tree[$strandKey]['sub_strands'][$subKey]['indicators'][$code];
                    continue;
                }

                // 2. Check for Content Standard (e.g. B7.1.1.1 Demonstrate understanding...)
                if (preg_match('/\b(B[1-9]\.(\d+)\.(\d+)\.\d+)\s+([A-Za-z].*?)(?:\s{4,}|$)/', $line, $m)) {
                    $stdCode = $m[1];
                    $stdDesc = trim($m[4]);
                    $gradeNum = (int) substr($stdCode, 1, strpos($stdCode, '.') - 1);
                    $gradeLabel = $gradeNum <= 6 
                        ? "Basic {$gradeNum}"
                        : "Basic {$gradeNum} (JHS " . ($gradeNum - 6) . ")";
                    $strandNum = (int) $m[2];
                    $subNum = (int) $m[3];

                    $strandKey = "{$gradeLabel}-{$strandNum}";
                    $subKey = "sub-{$subNum}";

                    if (isset($tree[$strandKey]['sub_strands'][$subKey])) {
                        $tree[$strandKey]['sub_strands'][$subKey]['content_standard'] = "{$stdCode} {$stdDesc}";
                    }
                    continue;
                }

                // 3. Collect Exemplars under the active indicator
                if ($currentIndicatorRef && preg_match('/^\s*(E\.g\.[,\s]*\d*[\.:]?|•|\-|\d+[\.\)])\s*(.+)$/i', $trimmed, $m)) {
                    $exemplarText = trim($trimmed);
                    if (empty($currentIndicatorRef['exemplars'])) {
                        $currentIndicatorRef['exemplars'] = $exemplarText;
                    } else {
                        $currentIndicatorRef['exemplars'] .= "\n" . $exemplarText;
                    }
                    continue;
                }

                // 4. Append continuous exemplar lines if indented
                if ($currentIndicatorRef && !empty($currentIndicatorRef['exemplars']) && strlen($trimmed) > 5) {
                    if (!preg_match('/^(CONTENT STANDARD|INDICATORS AND EXEMPLARS|CORE COMPETENCIES|=== PAGE|\d+$)/i', $trimmed)) {
                        $currentIndicatorRef['exemplars'] .= " " . $trimmed;
                    }
                }
            }
        }

        // Convert associative sub-strands and indicators to indexed arrays for clean DB storage
        $finalStrands = [];
        foreach ($tree as $sData) {
            $subList = [];
            foreach ($sData['sub_strands'] as $sub) {
                if (!empty($sub['indicators'])) {
                    $sub['indicators'] = array_values($sub['indicators']);
                    $subList[] = $sub;
                }
            }

            if (!empty($subList)) {
                $sData['sub_strands'] = $subList;
                $finalStrands[] = $sData;
            }
        }

        return [
            'curriculum_title' => $curriculum->title,
            'extraction_type' => 'deterministic_local',
            'strands' => $finalStrands,
        ];
    }

    /**
     * Map NaCCA code prefix (e.g. B7, B8, B9) to human-readable Grade Label.
     */
    protected function gradeFromCode(string $code): ?string
    {
        if (str_starts_with($code, 'B7.')) return 'Basic 7 (JHS 1)';
        if (str_starts_with($code, 'B8.')) return 'Basic 8 (JHS 2)';
        if (str_starts_with($code, 'B9.')) return 'Basic 9 (JHS 3)';
        if (str_starts_with($code, 'B1.')) return 'Basic 1';
        if (str_starts_with($code, 'B2.')) return 'Basic 2';
        if (str_starts_with($code, 'B3.')) return 'Basic 3';
        if (str_starts_with($code, 'B4.')) return 'Basic 4';
        if (str_starts_with($code, 'B5.')) return 'Basic 5';
        if (str_starts_with($code, 'B6.')) return 'Basic 6';
        return null;
    }

    /**
     * Resolve Level database ID from grade label.
     */
    protected function resolveLevelId(string $gradeLabel, ?int $defaultLevelId): ?int
    {
        $clean = strtolower(trim($gradeLabel));

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

        return $defaultLevelId;
    }
}
