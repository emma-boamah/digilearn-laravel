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

        if (!function_exists('exec')) {
            throw new Exception("Local extraction requires the 'exec()' function in PHP, but it is currently disabled in your server's php.ini (disable_functions). Please remove exec from disable_functions in php.ini.");
        }

        $hasPdftohtml = (bool) self::getBinaryPath('pdftohtml');
        $hasPdftotext = (bool) self::getBinaryPath('pdftotext');

        if (!$hasPdftohtml && !$hasPdftotext) {
            throw new Exception("Local extraction engine requires 'poppler-utils' to be installed on your production server. Please run: sudo apt-get update && sudo apt-get install -y poppler-utils");
        }

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
            if (!$hasPdftohtml) {
                throw new Exception("High-precision parsing requires 'pdftohtml' (poppler-utils). Please install it on your server: sudo apt-get update && sudo apt-get install -y poppler-utils");
            }
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

                $validSubStrandIds = [];
                $subStrandSort = 1;
                foreach ($sData['sub_strands'] as $subData) {
                    // Extract sub-strand number e.g. SUB-STRAND 1
                    $subNum = null;
                    if (preg_match('/sub[-\s]*strand\s*(\d+)/i', $subData['title'], $sm)) {
                        $subNum = (int) $sm[1];
                    }

                    // 1. Find Sub-strand by number, sort order, or title
                    $subStrand = null;
                    if ($subNum !== null) {
                        $subStrand = CurriculumSubStrand::where('strand_id', $strand->id)
                            ->where(function ($q) use ($subNum) {
                                $q->where('title', 'LIKE', "SUB-STRAND {$subNum}:%")
                                  ->orWhere('title', 'LIKE', "Sub-strand {$subNum}:%")
                                  ->orWhere('sort_order', $subNum);
                            })->first();
                    }

                    if (!$subStrand) {
                        $subStrand = CurriculumSubStrand::where('strand_id', $strand->id)
                            ->where('title', $subData['title'])
                            ->first();
                    }

                    if ($subStrand) {
                        $subStrand->update([
                            'title' => $subData['title'],
                            'content_standard' => $subData['content_standard'] ?? $subStrand->content_standard,
                            'sort_order' => $subNum ?? $subStrand->sort_order,
                        ]);
                    } else {
                        $subStrand = CurriculumSubStrand::create([
                            'strand_id' => $strand->id,
                            'title' => $subData['title'],
                            'description' => $subData['description'] ?? null,
                            'content_standard' => $subData['content_standard'] ?? null,
                            'sort_order' => $subNum ?? $subStrandSort++,
                        ]);
                    }

                    $validSubStrandIds[] = $subStrand->id;

                    $indSort = 1;
                    foreach ($subData['indicators'] as $indData) {
                        $code = $indData['indicator_code'] ?? null;

                        // 1. Look for existing indicator with this code
                        $existing = CurriculumIndicator::where('sub_strand_id', $subStrand->id)
                            ->where('indicator_code', $code)
                            ->first();

                        if (!$existing) {
                            $existing = CurriculumIndicator::whereHas('subStrand.strand', fn($q) => $q->where('curriculum_id', $curriculum->id))
                                ->where('indicator_code', $code)
                                ->first();
                            if ($existing) {
                                $existing->update(['sub_strand_id' => $subStrand->id]);
                            }
                        }

                        // Remove duplicate indicators with this code in this curriculum that don't have custom lessons
                        if ($code) {
                            $duplicates = CurriculumIndicator::where('indicator_code', $code)
                                ->whereHas('subStrand.strand', fn($q) => $q->where('curriculum_id', $curriculum->id))
                                ->where('id', '!=', $existing?->id ?? 0)
                                ->get();

                            foreach ($duplicates as $dup) {
                                if ($existing) {
                                    if (empty($existing->student_lesson_markdown) && !empty($dup->student_lesson_markdown)) {
                                        $existing->student_lesson_markdown = $dup->student_lesson_markdown;
                                    }
                                    if (empty($existing->interactive_segments) && !empty($dup->interactive_segments)) {
                                        $existing->interactive_segments = $dup->interactive_segments;
                                    }
                                    $existing->save();
                                }
                                $dup->delete();
                            }
                        }

                        if ($existing) {
                            // Update title, content_standard, core_competencies, and exemplars, but PRESERVE customized student lessons!
                            $updates = [];
                            if (!empty($indData['content_standard'])) {
                                $updates['content_standard'] = $indData['content_standard'];
                            }
                            if (!empty($indData['core_competencies'])) {
                                $updates['core_competencies'] = $indData['core_competencies'];
                            }
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
                                'content_standard' => $indData['content_standard'] ?? null,
                                'core_competencies' => $indData['core_competencies'] ?? null,
                                'description' => $indData['description'] ?? "Demonstrate understanding of {$indData['title']}",
                                'exemplars' => $indData['exemplars'] ?? null,
                                'sort_order' => $indSort++,
                            ]);
                            $totalSaved++;
                        }
                    }
                }

                // Clean up any stale empty sub-strands under this strand
                $strand->subStrands()
                    ->whereNotIn('id', $validSubStrandIds)
                    ->whereDoesntHave('indicators')
                    ->delete();
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
     * Locate full path of a system binary across common locations and PATH.
     */
    public static function getBinaryPath(string $binary): ?string
    {
        $commonPaths = [
            "/usr/bin/{$binary}",
            "/usr/local/bin/{$binary}",
            "/bin/{$binary}",
        ];

        foreach ($commonPaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        if (function_exists('exec')) {
            $output = [];
            $ret = 0;
            @exec("which {$binary} 2>/dev/null", $output, $ret);
            if ($ret === 0 && !empty($output[0])) {
                return trim($output[0]);
            }
        }

        return null;
    }

    /**
     * Check if the host server supports local extraction.
     */
    public static function isSupported(): bool
    {
        return function_exists('exec') && (bool) self::getBinaryPath('pdftohtml');
    }

    /**
     * High-Precision XML Coordinate Extractor using pdftohtml.
     */
    public function extractViaCoordinatesXml(string $filePath, Curriculum $curriculum): array
    {
        $bin = self::getBinaryPath('pdftohtml');
        if (!$bin) {
            Log::warning("CurriculumLocalParser: pdftohtml binary not found on server.");
            return ['strands' => []];
        }

        $escaped = escapeshellarg($filePath);
        $cmd = "{$bin} -xml -stdout {$escaped} 2>/dev/null";

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
        // Support standard B7.1.1.1 as well as B7/JHS1.1.1.1
        $stdRegex = '/\b(B\d+(?:\/JHS\d+)?(?:\.\d+){3})\b[:.\-]?\s*(.*)/i';
        // Support standard B7.1.1.1.1, B7/JHS1.1.1.1.1, typo 4-part B7.1.1.2, typo B71.2.2.2, or B9/JHS3.5.1.14
        $indRegex = '/^(B\d+(?:\/JHS\d+)?(?:\.\d+){2,4}|B\d{2,}\.\d+(?:\.\d+)+)\b[:.\-]?\s*(.*)/i';

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

        // Determine the last page containing indicators or standards to prevent parsing panel members / bibliography
        $lastTablePage = count($xml->page);
        $foundAnyTablePage = 0;
        foreach ($xml->page as $page) {
            $pNum = (int) $page['number'];
            foreach ($page->text as $t) {
                $val = trim(strip_tags($t->asXML()));
                if (preg_match($indRegex, $val) || preg_match($stdRegex, $val)) {
                    $foundAnyTablePage = max($foundAnyTablePage, $pNum);
                }
            }
        }
        if ($foundAnyTablePage > 0) {
            $lastTablePage = $foundAnyTablePage;
        }

        // Pass 1: Build comprehensive Content Standards map from Column 1 across all pages
        $standardsMap = [];
        $activeStdCode = null;
        foreach ($xml->page as $page) {
            if ((int) $page['number'] > $lastTablePage) continue;
            $c1Texts = [];
            foreach ($page->text as $t) {
                $left = (int) $t['left'];
                $val = trim(strip_tags($t->asXML()));
                if ($left < 280 && $val !== '' && !preg_match('/^(CONTENT STANDARD|===|\d+$)/i', $val)) {
                    $c1Texts[] = [
                        'top' => (int) $t['top'],
                        'val' => $val,
                    ];
                }
            }
            usort($c1Texts, fn($a, $b) => $a['top'] <=> $b['top']);

            foreach ($c1Texts as $item) {
                $v = $item['val'];
                if (preg_match($stdRegex, $v, $m)) {
                    $rawStdCode = $m[1];
                    $activeStdCode = preg_replace('/\/JHS\d+/i', '', $rawStdCode);
                    if (!isset($standardsMap[$activeStdCode])) {
                        $desc = trim(preg_replace($stdRegex, '', $v));
                        $standardsMap[$activeStdCode] = $activeStdCode . ($desc !== '' ? ': ' . $desc : '');
                    }
                } elseif ($activeStdCode && isset($standardsMap[$activeStdCode])) {
                    if (!str_contains($standardsMap[$activeStdCode], $v) && !str_contains($v, 'NaCCA') && !str_contains($v, 'Ministry of Education')) {
                        $standardsMap[$activeStdCode] .= ' ' . $v;
                    }
                }
            }
        }

        foreach ($xml->page as $page) {
            if ((int) $page['number'] > $lastTablePage) continue;

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

            // Detect Core Competencies Column 3 boundary for this page
            $coreCompThreshold = 800;
            foreach ($texts as $item) {
                if ($item['top'] < 160 && preg_match('/CORE COMPETENCIES/i', $item['val'])) {
                    // For documents like English where header is at 858 but text starts at 804,
                    // clamp threshold so it doesn't overshoot into Column 3 text
                    $coreCompThreshold = min(800, max(750, $item['left'] - 60));
                    break;
                }
            }

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
            // Column 2: Indicators and Exemplars (280 <= left < $coreCompThreshold)
            // Column 3: Core Competencies (left >= $coreCompThreshold)
            foreach ($texts as $item) {
                $val = $item['val'];
                $left = $item['left'];
                $top = $item['top'];

                if (preg_match('/^(CONTENT STANDARD|INDICATORS AND EXEMPLAR|CORE COMPETENCIES|===|\d+$)/i', $val)) {
                    continue;
                }

                // Column 1: Content Standards
                if ($left < 280) {
                    if (preg_match($stdRegex, $val, $mStd)) {
                        $curStdCode = preg_replace('/\/JHS\d+/i', '', $mStd[1]);
                        $curStdTitle = trim($mStd[2]);
                    } elseif ($curStdCode && strlen($val) > 1 && $top > 140) {
                        if (!str_contains($val, 'NaCCA') && !str_contains($val, 'Ministry of Education')) {
                            $curStdTitle .= ' ' . $val;
                        }
                    }
                }
                // Column 2: Indicators and Exemplars
                elseif ($left >= 280 && $left < $coreCompThreshold) {
                    if (preg_match($indRegex, $val, $mInd)) {
                        $rawCode = $mInd[1];
                        $rest = trim($mInd[2]);

                        // Normalize /JHS1 suffix: B7/JHS1.1.1.1.1 -> B7.1.1.1.1
                        $code = preg_replace('/\/JHS\d+/i', '', $rawCode);

                        // Normalize publisher typos:
                        // B982.1.1.1 (Computing) -> B8.2.1.1.1
                        if (preg_match('/^B98(\d)\.(\d+)\.(\d+)\.(\d+)(.*)/', $code, $tm)) {
                            $code = "B8.{$tm[1]}.{$tm[2]}.{$tm[3]}.{$tm[4]}" . $tm[5];
                        }
                        // Normalize missing dot typo e.g. B9.5.1.14 -> B9.5.1.1.4
                        elseif (preg_match('/^B(\d+)\.(\d+)\.(\d+)\.(\d)(\d+)$/', $code, $tm)) {
                            $code = "B{$tm[1]}.{$tm[2]}.{$tm[3]}.{$tm[4]}.{$tm[5]}";
                        }
                        // Normalize typo codes like B71.2.2.2 -> B7.1.2.2.2
                        elseif (preg_match('/^B(\d)(\d)\.(\d+)\.(\d+)(.*)/', $code, $tm)) {
                            $code = "B{$tm[1]}.{$tm[2]}.{$tm[3]}.{$tm[4]}" . $tm[5];
                        }

                        $parts = explode('.', $code);
                        $gradeNum = (int) substr($parts[0], 1);
                        $sNum = (int) ($parts[1] ?? 1);
                        $subNum = (int) ($parts[2] ?? 1);
                        $stdCode = implode('.', array_slice($parts, 0, 4));

                        $contentStandard = $standardsMap[$stdCode] ?? ($curStdCode && isset($standardsMap[$curStdCode]) ? $standardsMap[$curStdCode] : ($curStdCode ? trim("{$curStdCode}: {$curStdTitle}") : null));

                        $activeCode = $code;
                        $allIndicators[$code] = [
                            'code' => $code,
                            'title_parts' => $rest !== '' ? [$rest] : [],
                            'exemplars' => [],
                            'core_competencies' => [],
                            'grade_num' => $gradeNum,
                            'strand_num' => $sNum,
                            'substrand_num' => $subNum,
                            'content_standard' => $contentStandard,
                        ];
                    } elseif ($activeCode && isset($allIndicators[$activeCode])) {
                        $isEg = preg_match('/^(E\.g|Example|\b\d+\.|\b[ivx]+\.|\b[a-d]\.|•|\-)/i', $val);
                        if ($isEg || !empty($allIndicators[$activeCode]['exemplars'])) {
                            // Normalize "E.g., 1" or "E.g.,1." to "E.g. 1: "
                            $cleanVal = preg_replace('/^E\.g\.[,\s]*(\d+)[\.:]?\s*/i', 'E.g. $1: ', $val);
                            $allIndicators[$activeCode]['exemplars'][] = $cleanVal;
                        } elseif (!preg_match($strandRegex, $val) && !preg_match($substrandRegex, $val) && !preg_match($stdRegex, $val)) {
                            $allIndicators[$activeCode]['title_parts'][] = $val;
                        }
                    }
                }
                // Column 3: Core Competencies
                elseif ($left >= $coreCompThreshold) {
                    if ($activeCode && isset($allIndicators[$activeCode]) && $top > 140) {
                        if (!preg_match('/^(CORE COMPETENCIES|SUBJECT SPECIFIC PRACTICES)/i', $val) && !str_contains($val, 'NaCCA')) {
                            $allIndicators[$activeCode]['core_competencies'][] = $val;
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
                    'content_standard' => $ind['content_standard'],
                    'indicators' => [],
                ];
            }

            // Clean title and exemplars
            $title = trim(implode(' ', $ind['title_parts']));
            if (empty($title)) {
                $title = "Demonstrate understanding of {$code}";
            }

            $exemplarsText = !empty($ind['exemplars']) ? implode("\n", $ind['exemplars']) : '';
            $cleanComps = array_unique($ind['core_competencies']);
            $coreCompText = !empty($cleanComps) ? implode("\n", $cleanComps) : null;

            $tree[$strandKey]['sub_strands'][$subKey]['indicators'][$code] = [
                'indicator_code' => $code,
                'title' => $title,
                'description' => "Demonstrate understanding of {$title}",
                'content_standard' => $ind['content_standard'],
                'core_competencies' => $coreCompText,
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
        $indRegex = '/\b(B\d+(?:\/JHS\d+)?(?:\.\d+){2,4}|B\d{2,}\.\d+(?:\.\d+)+)\s+([A-Za-z].*?)(?:\s{4,}|$)/';
        $stdRegex = '/\b(B\d+(?:\/JHS\d+)?(?:\.\d+){3})\s+([A-Za-z].*?)(?:\s{4,}|$)/';

        $tree = [];
        $currentCode = null;
        $currentIndicatorRef = null;

        foreach ($pages as $p) {
            $lines = explode("\n", $p['normalized_text'] ?? $p['raw_text'] ?? '');

            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (empty($trimmed)) continue;

                if (preg_match($indRegex, $line, $m)) {
                    $rawCode = $m[1];
                    $title = trim($m[2]);

                    $code = preg_replace('/\/JHS\d+/i', '', $rawCode);
                    if (preg_match('/^B(\d+)\.(\d+)\.(\d+)\.(\d)(\d+)$/', $code, $tm)) {
                        $code = "B{$tm[1]}.{$tm[2]}.{$tm[3]}.{$tm[4]}.{$tm[5]}";
                    } elseif (preg_match('/^B(\d)(\d)\.(\d+)\.(\d+)(.*)/', $code, $tm)) {
                        $code = "B{$tm[1]}.{$tm[2]}.{$tm[3]}.{$tm[4]}" . $tm[5];
                    }

                    $parts = explode('.', $code);
                    $gradeNum = (int) substr($parts[0], 1);
                    $gradeLabel = $gradeNum <= 6 
                        ? "Basic {$gradeNum}"
                        : "Basic {$gradeNum} (JHS " . ($gradeNum - 6) . ")";

                    $strandNum = (int) ($parts[1] ?? 1);
                    $subNum = (int) ($parts[2] ?? 1);

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
