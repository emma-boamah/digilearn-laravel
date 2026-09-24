<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class PdfTextExtractionService
{
    /**
     * Get the total page count of a PDF file using pdfinfo or pdftotext.
     */
    public function getPageCount(string $filePath): int
    {
        if (!file_exists($filePath)) {
            return 0;
        }

        // 1. Try pdfinfo CLI (fastest & most accurate)
        if (function_exists('exec')) {
            $output = [];
            $returnVar = 0;
            $escaped = escapeshellarg($filePath);
            @exec("pdfinfo {$escaped} 2>&1", $output, $returnVar);

            if ($returnVar === 0) {
                foreach ($output as $line) {
                    if (preg_match('/^Pages:\s+(\d+)/i', trim($line), $matches)) {
                        return (int) $matches[1];
                    }
                }
            }
        }

        // 2. Fallback: extract with form-feeds and count page delimiters (\x0C)
        $pages = $this->extractAllPages($filePath);
        return count($pages);
    }

    /**
     * Extract all pages in a single fast pass using pdftotext with \x0C page delimiters.
     * Returns an array of page records keyed by 1-indexed page number:
     * [
     *   1 => ['page' => 1, 'raw_text' => '...', 'normalized_text' => '...', 'char_count' => 120],
     *   ...
     * ]
     */
    public function extractAllPages(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $rawOutput = '';
        if (function_exists('exec')) {
            $output = [];
            $returnVar = 0;
            $escaped = escapeshellarg($filePath);
            @exec("pdftotext -layout {$escaped} -", $output, $returnVar);
            if ($returnVar === 0) {
                $rawOutput = implode("\n", $output);
            }
        }

        if (empty($rawOutput)) {
            return [];
        }

        // pdftotext places a Form Feed character (\x0C or \f) at the end of each page
        $rawPages = explode("\x0C", $rawOutput);
        
        // Remove trailing empty element if present
        if (end($rawPages) === '') {
            array_pop($rawPages);
        }

        $pages = [];
        foreach ($rawPages as $idx => $pageText) {
            $pageNum = $idx + 1;
            $normalized = $this->normalizeText($pageText);
            $pages[$pageNum] = [
                'page' => $pageNum,
                'raw_text' => $pageText,
                'normalized_text' => $normalized,
                'char_count' => mb_strlen(trim($normalized)),
            ];
        }

        return $pages;
    }

    /**
     * Extract a specific page range using pdftotext.
     */
    public function extractPageRange(string $filePath, int $startPage, int $endPage): string
    {
        if (!file_exists($filePath)) {
            return '';
        }

        if (function_exists('exec')) {
            $output = [];
            $returnVar = 0;
            $escaped = escapeshellarg($filePath);
            $start = max(1, $startPage);
            $end = max($start, $endPage);
            @exec("pdftotext -f {$start} -l {$end} -layout {$escaped} -", $output, $returnVar);

            if ($returnVar === 0 && !empty($output)) {
                return $this->normalizeText(implode("\n", $output));
            }
        }

        return '';
    }

    /**
     * Normalize extracted text:
     * - Fix Windows/Mac line breaks
     * - Un-hyphenate words broken across lines (e.g. "curricu-\nlum" -> "curriculum")
     * - Normalize Unicode quotes and dashes
     * - Clean excessive spaces while preserving layout indentation and linebreaks
     */
    public function normalizeText(string $text): string
    {
        // 1. Line endings to \n
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // 2. Un-hyphenate split words: word-\nword -> wordword
        $text = preg_replace('/(\b\w{2,})-\n\s*(\w{2,}\b)/u', '$1$2', $text);

        // 3. Normalize Unicode quotes, apostrophes and dashes
        $replacements = [
            "\u{2018}" => "'", // Left single quote
            "\u{2019}" => "'", // Right single quote
            "\u{201C}" => '"', // Left double quote
            "\u{201D}" => '"', // Right double quote
            "\u{2013}" => '-', // En-dash
            "\u{2014}" => '--', // Em-dash
            "\u{00A0}" => ' ', // Non-breaking space
        ];
        $text = strtr($text, $replacements);

        // 4. Clean trailing whitespace on lines
        $lines = explode("\n", $text);
        $cleanLines = array_map(function ($line) {
            return rtrim($line);
        }, $lines);

        return implode("\n", $cleanLines);
    }

    /**
     * Detect if document appears to be scanned or contains almost no readable text,
     * indicating OCR is required.
     */
    public function detectOcrRequirement(array $pages): bool
    {
        if (empty($pages)) {
            return true;
        }

        $totalChars = 0;
        $pageCount = count($pages);

        foreach ($pages as $p) {
            $totalChars += $p['char_count'] ?? 0;
        }

        $avgChars = $pageCount > 0 ? ($totalChars / $pageCount) : 0;

        // If average characters per page is below 60 on a multi-page document, likely scanned
        return $pageCount >= 3 && $avgChars < 60;
    }
}
