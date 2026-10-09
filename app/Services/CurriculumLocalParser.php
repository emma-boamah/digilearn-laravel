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
    public const VERSION = 'v2.0-coordinate-engine';

    public function __construct(
        protected PdfTextExtractionService $pdfExtractor
    ) {}

    /**
     * Deterministically parse a curriculum PDF with 100% coordinate-aware table precision.
     */
    public function parse(Curriculum $curriculum): array
    {
        Log::info("CurriculumLocalParser: Starting coordinate-based parse for Curriculum ID {$curriculum->id}");

        $fullPath = Storage::disk('public')->path($curriculum->file_path);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/public/' . $curriculum->file_path);
        }

        if (!file_exists($fullPath)) {
            throw new Exception("Curriculum PDF not found at path: {$curriculum->file_path}");
        }

        $fileSize = filesize($fullPath);
        $pageCount = $this->pdfExtractor->getPageCount($fullPath);

        $curriculum->update([
            'file_size_bytes' => $fileSize,
            'source_page_count' => $pageCount,
        ]);

        // 1. Attempt high-precision coordinate extraction via pdftohtml XML
        $parsedStructure = $this->extractViaCoordinatesXml($fullPath, $curriculum);

        // 2. If XML extraction produced no strands, fallback to text-stream
        if (empty($parsedStructure['strands'])) {
            Log::warning("CurriculumLocalParser: XML extraction produced 0 strands, trying text-stream fallback.");
            $pages = $this->pdfExtractor->extractAllPages($fullPath);
            $parsedStructure = $this->extractStructureFromPages($pages, $curriculum);
        }

        if (empty($parsedStructure['strands'])) {
            throw new Exception("Could not identify standard curriculum strands or indicators in this document.");
        }

        // 3. Persist the extracted structure with intelligent sync (preserving customized lesson content)
        $totalSaved = 0;
        DB::transaction(function () use ($curriculum, $parsedStructure, &$totalSaved) {
            $validStrandIds = [];

            $strandSort = 1;
            foreach ($parsedStructure['strands'] as $sData) {
                $levelId = $this->resolveLevelId($sData['grade_label'] ?? '', $curriculum->level_id);

                // Find or create Strand
                $strand = CurriculumStrand::firstOrCreate(
                    [
                        'curriculum_id' => $curriculum->id,
                        'title' => $sData['title'],
                        'grade_label' => $sData['grade_label'] ?? null,
                    ],
                    [
                        'description' => $sData['description'] ?? 'Curriculum strand covering ' . $sData['title'],
                        'sort_order' => $strandSort++,
                        'level_id' => $levelId,
                    ]
                );

                if (!$strand->level_id && $levelId) {
                    $strand->update(['level_id' => $levelId]);
                }

                $validStrandIds[] = $strand->id;

                $subStrandSort = 1;
                foreach ($sData['sub_strands'] as $subData) {
                    // Find or create Sub-strand
                    $subStrand = CurriculumSubStrand::firstOrCreate(
                        [
                            'strand_id' => $strand->id,
                            'title' => $subData['title'],
                        ],
                        [
                            'description' => $subData['description'] ?? null,
                            'content_standard' => $subData['content_standard'] ?? null,
                            'sort_order' => $subStrandSort++,
                        ]
                    );

                    if (!empty($subData['content_standard']) && empty($subStrand->content_standard)) {
                        $subStrand->update(['content_standard' => $subData['content_standard']]);
                    }

                    $indSort = 1;
                    foreach ($subData['indicators'] as $indData) {
                        $code = $indData['indicator_code'] ?? null;

                        $existing = CurriculumIndicator::where('sub_strand_id', $subStrand->id)
                            ->where('indicator_code', $code)
                            ->first();

                        if ($existing) {
                            // Update title and exemplars if more complete, but PRESERVE customized student lessons!
                            $updates = [];
                            if (strlen($indData['title']) > strlen($existing->title)) {
                                $updates['title'] = $indData['title'];
                            }
                            if (!empty($indData['exemplars']) && (empty($existing->exemplars) || strlen($indData['exemplars']) > strlen($existing->exemplars))) {
                                $updates['exemplars'] = $indData['exemplars'];
                            }
                            if (!empty($updates)) {
                                $existing->update($updates);
                            }
                            $totalSaved++;
                        } else {
                            CurriculumIndicator::create([
                                'sub_strand_id' => $subStrand->id,
                                'indicator_code' => $code,
                                'title' => $indData['title'],
                                'description' => $indData['description'] ?? "Demonstrate understanding of {$indData['title']}",
                                'exemplars' => $indData['exemplars'] ?? null,
                                'sort_order' => $indSort++,
                            ]);
                            $totalSaved++;
                        }
                    }
                }
            }

            // Clean up any stale empty strands from previous buggy runs that have 0 indicators
            $curriculum->strands()
                ->whereNotIn('id', $validStrandIds)
                ->whereDoesntHave('subStrands.indicators')
                ->delete();

            $curriculum->update([
                'extraction_status' => 'extracted',
                'extraction_method' => 'local',
                'extraction_quality' => 'high',
                'requires_review' => false,
                'local_parser_version' => self::VERSION,
                'raw_extraction_json' => $parsedStructure,
                'extraction_error' => null,
            ]);
        });

        Log::info("CurriculumLocalParser: Completed successfully for Curriculum ID {$curriculum->id}. Saved {$totalSaved} indicators across " . count($parsedStructure['strands']) . " strands.");

        return $parsedStructure;
    }

    /**
     * High-Precision XML Coordinate Extractor using pdftohtml.
     */
    public function extractViaCoordinatesXml(string $filePath, Curriculum $curriculum): array
    {
        $escaped = escapeshellarg($filePath);
        $cmd = "pdftohtml -xml -stdout {$escaped} 2>/dev/null";

        $output = [];
        $returnVar = 0;
        @exec($cmd, $output, $returnVar);

        if ($returnVar !== 0 || empty($output)) {
            return ['strands' => []];
        }

        $xmlString = implode("
", $output);
        $xml = @simplexml_load_string($xmlString);

        if (!$xml || !isset($xml->page)) {
            return ['strands' => []];
        }

        // Negative lookbehind ensures we NEVER match "Sub-strand" as "Strand"
        $strandRegex = '/(?<!sub[-\s])\bstrand\s+(\d+)\s*[:.\-]?\s*(.*)/i';
        $substrandRegex = '/\bSub[-\s]?strand\s+(\d+)\s*[:.\-]?\s*(.*)/i';
        $stdRegex = '/\b(B\d+\.\d+\.\d+\.\d+)\b(.*)/i';
        $indRegex = '/\b(B\d+\.\d+\.\d+\.\d+\.\d+)\b(.*)/i';

        // Discovered titles
        $strandTitles = [
            1 => 'NUMBER',
            2 => 'ALGEBRA',
            3 => 'GEOMETRY AND MEASUREMENT',
            4 => 'HANDLING DATA',
        ];
        $substrandTitles = [];

        $allIndicators = [];
        $activeCode = null;

        $curStdCode = null;
        $curStdTitle = '';

        $curStrandNum = 1;

        foreach ($xml->page as $page) {
            $texts = [];
            foreach ($page->text as $t) {
                // Must use strip_tags to capture text inside <b> and <i> tags!
                $rawVal = trim(strip_tags($t->asXML()));
                if ($rawVal === '') continue;
                $texts[] = [
                    'left' => (int) $t['left'],
                    'top' => (int) $t['top'],
                    'val' => $rawVal,
                ];
            }

            if (empty($texts)) continue;

            // Sort top-to-bottom, then left-to-right
            usort($texts, function ($a, $b) {
                if ($a['top'] === $b['top']) {
                    return $a['left'] <=> $b['left'];
                }
                return $a['top'] <=> $b['top'];
            });

            // 1. Scan for Strand and Sub-strand headers (typically top < 160)
            foreach ($texts as $item) {
                $val = $item['val'];
                $left = $item['left'];
                $top = $item['top'];

                if ($top < 160 && $left < 850 && !preg_match($indRegex, $val)) {
                    if (preg_match($strandRegex, $val, $sm)) {
                        $sNum = (int) $sm[1];
                        $sName = trim(preg_replace('/\.{2,}.*$/', '', $sm[2]));
                        if (strlen($sName) > 1) {
                            $curStrandNum = $sNum;
                            $strandTitles[$sNum] = $sName;
                        }
                    }
                    if (preg_match($substrandRegex, $val, $subm)) {
                        $subNum = (int) $subm[1];
                        $subName = trim(preg_replace('/\.{2,}.*$/', '', $subm[2]));
                        if (strlen($subName) > 1) {
                            $substrandTitles[$curStrandNum][$subNum] = $subName;
                        }
                    }
                }
            }

            // 2. Scan Columns:
            // Column 1: Content Standard (left < 280)
            // Column 2: Indicators and Exemplars (280 <= left < 910)
            foreach ($texts as $item) {
                $val = $item['val'];
                $left = $item['left'];
                $top = $item['top'];

                if ($val === 'CONTENT STANDARD' || $val === 'INDICATORS AND EXEMPLARS' || $val === 'CORE COMPETENCIES') {
                    continue;
                }

                // Column 1: Content Standards
                if ($left < 280) {
                    if (preg_match($stdRegex, $val, $mStd)) {
                        $curStdCode = $mStd[1];
                        $curStdTitle = trim($mStd[2]);
                    } elseif ($curStdCode && strlen($val) > 1 && $top > 140) {
                        $curStdTitle .= ' ' . $val;
                    }
                }
                // Column 2: Indicators and Exemplars
                elseif ($left >= 280 && $left < 910) {
                    if (preg_match($indRegex, $val, $mInd)) {
                        $code = $mInd[1];
                        $idx = strpos($val, $code);
                        $rest = trim(substr($val, $idx + strlen($code)));

                        $parts = explode('.', $code);
                        $gradeNum = (int) substr($parts[0], 1);
                        $sNum = (int) ($parts[1] ?? 1);
                        $subNum = (int) ($parts[2] ?? 1);
                        $stdCode = implode('.', array_slice($parts, 0, 4));

                        $activeCode = $code;
                        $allIndicators[$code] = [
                            'code' => $code,
                            'title_parts' => $rest !== '' ? [$rest] : [],
                            'exemplars' => [],
                            'grade_num' => $gradeNum,
                            'strand_num' => $sNum,
                            'substrand_num' => $subNum,
                            'std_code' => $stdCode,
                            'std_title' => ($curStdCode === $stdCode) ? $curStdTitle : '',
                        ];
                    } elseif ($activeCode && isset($allIndicators[$activeCode])) {
                        $isEg = preg_match('/^(E\.g|Example|\b\d+\.|\b[ivx]+\.|\b[a-d]\.|•|\-)/i', $val);
                        if ($isEg || !empty($allIndicators[$activeCode]['exemplars'])) {
                            $allIndicators[$activeCode]['exemplars'][] = $val;
                        } elseif (!preg_match($strandRegex, $val) && !preg_match($substrandRegex, $val) && !preg_match($stdRegex, $val)) {
                            $allIndicators[$activeCode]['title_parts'][] = $val;
                        }
                    }
                }
            }
        }

        if (empty($allIndicators)) {
            return ['strands' => []];
        }

        // 3. Assemble Hierarchical Tree: Grade -> Strand -> Sub-strand -> Indicators
        $tree = [];

        foreach ($allIndicators as $code => $ind) {
            $gradeNum = $ind['grade_num'];
            $gradeLabel = $gradeNum <= 6
                ? "Basic {$gradeNum}"
                : "Basic {$gradeNum} (JHS " . ($gradeNum - 6) . ")";

            $sNum = $ind['strand_num'];
            $subNum = $ind['substrand_num'];

            $strandKey = "{$gradeLabel}-{$sNum}";
            if (!isset($tree[$strandKey])) {
                $sName = $strandTitles[$sNum] ?? "STRAND {$sNum}";
                $tree[$strandKey] = [
                    'grade_label' => $gradeLabel,
                    'title' => "STRAND {$sNum}: " . mb_strtoupper($sName),
                    'description' => "Curriculum strand covering {$sName}.",
                    'sub_strands' => [],
                ];
            }

            $subKey = "sub-{$subNum}";
            if (!isset($tree[$strandKey]['sub_strands'][$subKey])) {
                $subName = $substrandTitles[$sNum][$subNum] ?? "Sub-strand {$subNum}";
                $tree[$strandKey]['sub_strands'][$subKey] = [
                    'title' => "SUB-STRAND {$subNum}: " . mb_strtoupper($subName),
                    'content_standard' => $ind['std_title'] ? "{$ind['std_code']} {$ind['std_title']}" : null,
                    'indicators' => [],
                ];
            }

            // Clean title and exemplars
            $title = trim(implode(' ', $ind['title_parts']));
            if (empty($title)) {
                $title = "Demonstrate understanding of {$code}";
            }

            $exemplarsText = !empty($ind['exemplars']) ? implode("
", $ind['exemplars']) : '';

            $tree[$strandKey]['sub_strands'][$subKey]['indicators'][$code] = [
                'indicator_code' => $code,
                'title' => $title,
                'description' => "Demonstrate understanding of {$title}",
                'exemplars' => $exemplarsText,
            ];
        }

        // Format for return
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
            'extraction_type' => 'coordinate_engine_v2',
            'strands' => $finalStrands,
        ];
    }

    /**
     * Fallback text-stream parser (if pdftohtml is unavailable).
     */
    public function extractStructureFromPages(array $pages, Curriculum $curriculum): array
    {
        $indRegex = '/\b(B[1-9]\.(\d+)\.(\d+)\.\d+\.\d+)\s+([A-Za-z].*?)(?:\s{4,}|$)/';
        $stdRegex = '/\b(B[1-9]\.(\d+)\.(\d+)\.\d+)\s+([A-Za-z].*?)(?:\s{4,}|$)/';

        $tree = [];
        $currentCode = null;
        $currentIndicatorRef = null;

        foreach ($pages as $p) {
            $lines = explode("
", $p['normalized_text'] ?? $p['raw_text'] ?? '');

            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (empty($trimmed)) continue;

                if (preg_match($indRegex, $line, $m)) {
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
                        $tree[$strandKey] = [
                            'grade_label' => $gradeLabel,
                            'title' => "STRAND {$strandNum}",
                            'description' => "Curriculum strand {$strandNum}",
                            'sub_strands' => [],
                        ];
                    }

                    $subKey = "sub-{$subNum}";
                    if (!isset($tree[$strandKey]['sub_strands'][$subKey])) {
                        $tree[$strandKey]['sub_strands'][$subKey] = [
                            'title' => "SUB-STRAND {$subNum}",
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

                if (preg_match($stdRegex, $line, $m)) {
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

                if ($currentIndicatorRef && preg_match('/^\s*(E\.g\.[,\s]*\d*[\.:]?|•|\-|\d+[\.\)])\s*(.+)$/i', $trimmed, $m)) {
                    $exemplarText = trim($trimmed);
                    if (empty($currentIndicatorRef['exemplars'])) {
                        $currentIndicatorRef['exemplars'] = $exemplarText;
                    } else {
                        $currentIndicatorRef['exemplars'] .= "
" . $exemplarText;
                    }
                }
            }
        }

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
            'extraction_type' => 'text_stream_fallback',
            'strands' => $finalStrands,
        ];
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

        for ($i = 1; $i <= 6; $i++) {
            if (str_contains($clean, "primary {$i}") || str_contains($clean, "basic {$i}") || str_contains($clean, "b{$i}")) {
                return Level::where('slug', "primary-{$i}")->value('id') ?? $defaultLevelId;
            }
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
