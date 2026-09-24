<?php

namespace App\Services;

use App\Models\Textbook;
use App\Models\TextbookChapter;
use App\Models\TextbookSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class TextbookLocalParser
{
    public const VERSION = 'v1.0-deterministic';
    public const MAX_TOC_PAGES = 50;

    public function __construct(
        protected PdfTextExtractionService $pdfExtractor
    ) {}

    /**
     * Deterministically extract Table of Contents (TOC) from the first 50 pages.
     */
    public function parseTableOfContents(Textbook $textbook): array
    {
        Log::info("TextbookLocalParser: Starting TOC extraction for Textbook ID {$textbook->id}");

        $fullPath = Storage::disk('public')->path($textbook->file_path);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/public/' . $textbook->file_path);
        }

        if (!file_exists($fullPath)) {
            throw new Exception("Textbook PDF not found at path: {$textbook->file_path}");
        }

        $totalPages = $this->pdfExtractor->getPageCount($fullPath);
        $textbook->update([
            'total_pages' => $totalPages,
            'file_size_bytes' => filesize($fullPath),
        ]);

        // 1. Extract first 50 pages for TOC search
        $scanLimit = min($totalPages > 0 ? $totalPages : self::MAX_TOC_PAGES, self::MAX_TOC_PAGES);
        $tocText = $this->pdfExtractor->extractPageRange($fullPath, 1, $scanLimit);

        if (empty($tocText)) {
            throw new Exception("No text could be read from the first {$scanLimit} pages of the textbook.");
        }

        // 2. Parse TOC entries from text
        $parsedChapters = $this->extractChaptersFromTocText($tocText, $totalPages);

        if (empty($parsedChapters)) {
            // Fallback: If no TOC section was found, scan for chapter headings in body
            $parsedChapters = $this->inferChaptersFromBody($fullPath, $totalPages);
        }

        if (empty($parsedChapters)) {
            throw new Exception("Local parser could not find Table of Contents or chapter headings in the first {$scanLimit} pages.");
        }

        // 3. Persist chapters and sections in database
        DB::transaction(function () use ($textbook, $parsedChapters) {
            $textbook->chapters()->delete();

            $chapterSort = 1;
            foreach ($parsedChapters as $chData) {
                $chapter = TextbookChapter::create([
                    'textbook_id' => $textbook->id,
                    'title' => $chData['title'],
                    'page_start' => $chData['page_start'] ?? null,
                    'page_end' => $chData['page_end'] ?? null,
                    'sort_order' => $chapterSort++,
                ]);

                $sectionSort = 1;
                foreach ($chData['sections'] ?? [] as $secData) {
                    TextbookSection::create([
                        'chapter_id' => $chapter->id,
                        'title' => $secData['title'],
                        'page_start' => $secData['page_start'] ?? null,
                        'page_end' => $secData['page_end'] ?? null,
                        'sort_order' => $sectionSort++,
                    ]);
                }
            }

            $textbook->update([
                'toc_extraction_status' => 'extracted',
                'extraction_method' => 'local',
                'extraction_quality' => count($parsedChapters) >= 3 ? 'high' : 'medium',
                'requires_review' => true,
                'local_parser_version' => self::VERSION,
                'raw_toc_json' => ['chapters' => $parsedChapters],
                'extraction_error' => null,
            ]);
        });

        Log::info("TextbookLocalParser: Successfully extracted " . count($parsedChapters) . " chapters for Textbook ID {$textbook->id}");

        return ['chapters' => $parsedChapters];
    }

    /**
     * Deterministically parse chapter text content from its assigned page range.
     */
    public function parseChapterContent(TextbookChapter $chapter): void
    {
        $textbook = $chapter->textbook;
        $fullPath = Storage::disk('public')->path($textbook->file_path);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/public/' . $textbook->file_path);
        }

        if (!$chapter->page_start) {
            Log::warning("Cannot extract local content for chapter {$chapter->id}: page_start not set.");
            return;
        }

        $start = (int) $chapter->page_start;
        $end = $chapter->page_end ? (int) $chapter->page_end : ($start + 15);

        $chapterRawText = $this->pdfExtractor->extractPageRange($fullPath, $start, $end);

        if (empty($chapterRawText)) {
            return;
        }

        $sections = $chapter->sections()->orderBy('sort_order')->get();

        if ($sections->isEmpty()) {
            // Create a default main section for the chapter
            $section = TextbookSection::create([
                'chapter_id' => $chapter->id,
                'title' => $chapter->title,
                'sort_order' => 1,
                'page_start' => $start,
                'page_end' => $end,
            ]);
            $sections = collect([$section]);
        }

        // Convert raw text into structured HTML/Markdown
        $formattedHtml = $this->convertRawTextToHtml($chapterRawText);
        $formattedMarkdown = $this->convertRawTextToMarkdown($chapterRawText);

        // Assign to first section (or distribute if section page ranges are known)
        $firstSection = $sections->first();
        $firstSection->update([
            'content_markdown' => $formattedMarkdown,
            'content_html' => $formattedHtml,
        ]);

        $textbook->update([
            'content_extraction_status' => 'extracted',
            'extraction_method' => 'local',
            'requires_review' => true,
        ]);
    }

    /**
     * Parse Table of Contents lines.
     */
    protected function extractChaptersFromTocText(string $tocText, int $totalPages): array
    {
        $lines = explode("\n", $tocText);
        $inToc = false;
        $tocEntries = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) continue;

            // Detect TOC beginning
            if (preg_match('/^(TABLE OF CONTENTS|CONTENTS|COURSE OUTLINE)/i', $trimmed)) {
                $inToc = true;
                continue;
            }

            // If we have found TOC, stop if we hit an index, preface, or clear end
            if ($inToc && preg_match('/^(PREFACE|FOREWORD|INTRODUCTION)\s*$/i', $trimmed) && count($tocEntries) > 2) {
                // If it's after many entries, likely body started
                break;
            }

            // Match TOC line with dot leaders or trailing page number:
            // "Chapter 1: Whole Numbers ....... 15"
            // "1. Number Systems ............. 1"
            // "Unit 3: Algebra    45"
            if (preg_match('/^(.*?)(?:\.{2,}|\s{3,})(\d+)\s*$/u', $trimmed, $m)) {
                $titlePart = trim($m[1]);
                $pagePart = (int) $m[2];

                // Skip non-chapter lines (e.g. "Page ... 1")
                if (preg_match('/^(page|contents|index)/i', $titlePart)) {
                    continue;
                }

                if (!empty($titlePart) && $pagePart > 0) {
                    $tocEntries[] = [
                        'raw_title' => $titlePart,
                        'page' => $pagePart,
                    ];
                }
            }
        }

        if (empty($tocEntries)) {
            return [];
        }

        // Group TOC entries into Chapters and Sections
        $chapters = [];
        $currentChapter = null;

        foreach ($tocEntries as $entry) {
            $title = $entry['raw_title'];
            $page = $entry['page'];

            $isChapter = preg_match('/^(chapter|unit|module|theme|part)\s+\d+/i', $title)
                || preg_match('/^\d+\.\s+[A-Za-z]/', $title);

            $isSection = preg_match('/^\d+\.\d+/', $title);

            if ($isChapter || !$currentChapter) {
                if ($currentChapter) {
                    $chapters[] = $currentChapter;
                }
                $currentChapter = [
                    'title' => $title,
                    'page_start' => $page,
                    'page_end' => null,
                    'sections' => [],
                ];
            } elseif ($isSection && $currentChapter) {
                $currentChapter['sections'][] = [
                    'title' => $title,
                    'page_start' => $page,
                    'page_end' => null,
                ];
            } elseif ($currentChapter) {
                // Additional sub-topic entry
                $currentChapter['sections'][] = [
                    'title' => $title,
                    'page_start' => $page,
                    'page_end' => null,
                ];
            }
        }

        if ($currentChapter) {
            $chapters[] = $currentChapter;
        }

        // Calculate page_end for each chapter and section based on subsequent entries
        for ($i = 0; $i < count($chapters); $i++) {
            $nextChapterStart = isset($chapters[$i + 1]) ? $chapters[$i + 1]['page_start'] : ($totalPages > 0 ? $totalPages : $chapters[$i]['page_start'] + 20);
            $chapters[$i]['page_end'] = max($chapters[$i]['page_start'], $nextChapterStart - 1);

            // Calculate section page_end
            $secCount = count($chapters[$i]['sections']);
            for ($j = 0; $j < $secCount; $j++) {
                $nextSecStart = isset($chapters[$i]['sections'][$j + 1])
                    ? $chapters[$i]['sections'][$j + 1]['page_start']
                    : $chapters[$i]['page_end'];
                $chapters[$i]['sections'][$j]['page_end'] = max($chapters[$i]['sections'][$j]['page_start'], $nextSecStart - 1);
            }
        }

        return $chapters;
    }

    /**
     * Fallback: Infer chapters from prominent headings across the document if no TOC exists.
     */
    protected function inferChaptersFromBody(string $filePath, int $totalPages): array
    {
        $scanPages = min($totalPages > 0 ? $totalPages : 60, 60);
        $bodyText = $this->pdfExtractor->extractPageRange($filePath, 1, $scanPages);
        $lines = explode("\n", $bodyText);

        $chapters = [];
        $currentChapter = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^(CHAPTER|UNIT|MODULE)\s+(\d+)[:.\-]?\s*(.+)$/i', $trimmed, $m)) {
                $chNum = (int) $m[2];
                $chName = trim($m[3]);
                $title = "Chapter {$chNum}: {$chName}";

                $chapters[] = [
                    'title' => $title,
                    'page_start' => count($chapters) * 20 + 1,
                    'page_end' => count($chapters) * 20 + 20,
                    'sections' => [],
                ];
            }
        }

        return $chapters;
    }

    /**
     * Convert raw extracted text into clean, safe HTML without hallucinating summaries.
     */
    protected function convertRawTextToHtml(string $rawText): string
    {
        $paragraphs = preg_split('/\n\s*\n/', trim($rawText));
        $html = "<div class='prose dark:prose-invert max-w-none text-sm leading-relaxed space-y-4'>\n";

        foreach ($paragraphs as $para) {
            $para = trim($para);
            if (empty($para)) continue;

            // Headings: short lines in all-caps or numbered
            if (strlen($para) < 70 && (ctype_upper(str_replace([' ', ':', '-', '.'], '', $para)) || preg_match('/^\d+\.\d+\s+/', $para))) {
                $html .= "<h3 class='text-lg font-bold text-gray-900 dark:text-white mt-4'>" . htmlspecialchars($para) . "</h3>\n";
                continue;
            }

            // Bullet lists
            if (preg_match('/^[-•*]\s+/m', $para)) {
                $items = preg_split('/\n[-•*]\s+/', $para);
                $html .= "<ul class='list-disc pl-5 space-y-1 my-2'>\n";
                foreach ($items as $item) {
                    $item = trim(ltrim($item, "-•* "));
                    if (!empty($item)) {
                        $html .= "<li>" . htmlspecialchars($item) . "</li>\n";
                    }
                }
                $html .= "</ul>\n";
                continue;
            }

            // Numbered lists
            if (preg_match('/^\d+[\.\)]\s+/m', $para)) {
                $items = preg_split('/\n\d+[\.\)]\s+/', $para);
                $html .= "<ol class='list-decimal pl-5 space-y-1 my-2'>\n";
                foreach ($items as $item) {
                    $item = trim(ltrim($item, "0..9.) "));
                    if (!empty($item)) {
                        $html .= "<li>" . htmlspecialchars($item) . "</li>\n";
                    }
                }
                $html .= "ol>\n";
                continue;
            }

            // Standard paragraph
            $html .= "<p>" . nl2br(htmlspecialchars($para)) . "</p>\n";
        }

        $html .= "</div>";
        return $html;
    }

    /**
     * Convert raw text to basic markdown.
     */
    protected function convertRawTextToMarkdown(string $rawText): string
    {
        return trim($rawText);
    }
}
