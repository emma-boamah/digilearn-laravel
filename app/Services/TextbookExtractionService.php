<?php

namespace App\Services;

use App\Models\Textbook;
use App\Models\TextbookChapter;
use App\Models\TextbookSection;
use App\Models\TextbookMedia;
use App\Models\CurriculumStrand;
use App\Models\CurriculumIndicator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class TextbookExtractionService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));
        $this->model = config('services.gemini.model', 'gemini-flash-latest');
    }

    /**
     * Extract Table of Contents from a textbook PDF.
     */
    public function extractTableOfContents(Textbook $textbook): array
    {
        $textbook->update([
            'toc_extraction_status' => 'processing',
        ]);

        try {
            $fullPath = Storage::disk('public')->path($textbook->file_path);
            if (!file_exists($fullPath)) {
                $fullPath = storage_path('app/public/' . $textbook->file_path);
            }
            if (!file_exists($fullPath)) {
                throw new Exception("Textbook PDF file not found at path: {$textbook->file_path}");
            }

            $fileSize = filesize($fullPath);
            $textbook->update(['file_size_bytes' => $fileSize]);

            $fileBase64 = null;
            $extractedText = null;

            if ($fileSize < 20 * 1024 * 1024) {
                $fileBase64 = base64_encode(file_get_contents($fullPath));
            }

            if (function_exists('exec')) {
                $output = [];
                $returnVar = 0;
                $escaped = escapeshellarg($fullPath);
                // Extract first 25 pages which typically contain the TOC
                @exec("pdftotext -f 1 -l 25 -layout {$escaped} -", $output, $returnVar);
                if ($returnVar === 0 && !empty($output)) {
                    $extractedText = implode("\n", $output);
                }
            }

            $prompt = <<<PROMPT
You are an expert textbook analyst and educational curriculum specialist.
Examine this textbook titled "{$textbook->title}" for Subject "{$textbook->subject->name}".

Extract the complete Table of Contents (TOC) with:
1. Every Chapter / Unit / Module
2. The starting page number and ending page number (if available)
3. Sub-sections / sub-topics within each chapter and their page numbers

Output MUST be strictly valid JSON without any markdown code fence backticks or commentary:
{
  "book_title": "{$textbook->title}",
  "chapters": [
    {
      "title": "Chapter 1: Name of Chapter",
      "page_start": 1,
      "page_end": 28,
      "sections": [
        {
          "title": "1.1 Sub-topic Name",
          "page_start": 1,
          "page_end": 12
        },
        {
          "title": "1.2 Sub-topic Name",
          "page_start": 13,
          "page_end": 28
        }
      ]
    }
  ]
}
PROMPT;

            $rawResponse = $this->queryGemini($prompt, $fileBase64, $extractedText);
            $cleanJson = trim(preg_replace('/^```(?:json)?|```$/m', '', $rawResponse));
            $data = json_decode($cleanJson, true);

            if (!is_array($data) || empty($data['chapters'])) {
                throw new Exception("TOC extraction did not return valid chapters list. Response preview: " . substr($rawResponse, 0, 300));
            }

            DB::transaction(function () use ($textbook, $data) {
                // Remove existing chapters if re-extracting
                $textbook->chapters()->delete();

                $chapterSort = 1;
                foreach ($data['chapters'] as $chData) {
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
                    'raw_toc_json' => $data,
                ]);
            });

            Log::info("Textbook TOC extraction completed successfully for ID {$textbook->id}");
            return $data;

        } catch (\Throwable $e) {
            Log::error("Textbook TOC extraction failed for ID {$textbook->id}: " . $e->getMessage());
            $textbook->update([
                'toc_extraction_status' => 'failed',
            ]);
            throw $e;
        }
    }

    /**
     * Extract full teaching content for a specific chapter or section.
     */
    public function extractChapterContent(TextbookChapter $chapter): void
    {
        $textbook = $chapter->textbook;
        $fullPath = Storage::disk('public')->path($textbook->file_path);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/public/' . $textbook->file_path);
        }

        $pageContext = '';
        if ($chapter->page_start && $chapter->page_end && function_exists('exec')) {
            $output = [];
            $returnVar = 0;
            $start = (int) $chapter->page_start;
            $end = (int) $chapter->page_end;
            $escaped = escapeshellarg($fullPath);
            @exec("pdftotext -f {$start} -l {$end} -layout {$escaped} -", $output, $returnVar);
            if ($returnVar === 0 && !empty($output)) {
                $pageContext = implode("\n", $output);
            }
        }

        $prompt = <<<PROMPT
You are an expert instructional designer transforming a textbook chapter into an engaging, high-yield digital learning topic (similar to freeCodeCamp / Khan Academy / Brilliant).

Chapter Title: "{$chapter->title}"
Subject: "{$textbook->subject->name}"

Extract and write comprehensive, clear learning content for every sub-section in this chapter.
For each sub-section, include:
- Clear, plain-English conceptual explanations
- Key definitions, axioms, or rules
- Mathematical formulas formatted in standard LaTeX ($...$ and $$...$$)
- Step-by-step worked examples
- Summary takeaway checkpoints

Output MUST be strictly valid JSON without markdown code fences:
{
  "chapter_title": "{$chapter->title}",
  "sections": [
    {
      "title": "Exact Sub-topic Title",
      "content_markdown": "Full educational explanation in clean Markdown with LaTeX math and code blocks.",
      "content_html": "<div class='prose max-w-none'><h3>Topic Title</h3><p>Explanation...</p></div>"
    }
  ]
}
PROMPT;

        $rawResponse = $this->queryGemini($prompt, null, $pageContext);
        $cleanJson = trim(preg_replace('/^```(?:json)?|```$/m', '', $rawResponse));
        $data = json_decode($cleanJson, true);

        if (!is_array($data) || empty($data['sections'])) {
            throw new Exception("Chapter content extraction returned invalid format.");
        }

        DB::transaction(function () use ($chapter, $data) {
            foreach ($data['sections'] as $index => $secData) {
                // Find existing matching section or create
                $section = TextbookSection::where('chapter_id', $chapter->id)
                    ->where('title', 'like', '%' . mb_substr($secData['title'], 0, 30) . '%')
                    ->first();

                if (!$section) {
                    $section = TextbookSection::create([
                        'chapter_id' => $chapter->id,
                        'title' => $secData['title'],
                        'sort_order' => $index + 1,
                    ]);
                }

                $section->update([
                    'content_markdown' => $secData['content_markdown'] ?? null,
                    'content_html' => $secData['content_html'] ?? $this->renderMarkdownToHtml($secData['content_markdown'] ?? ''),
                ]);
            }
        });
    }

    /**
     * Helper to render markdown string to HTML.
     */
    protected function renderMarkdownToHtml(string $markdown): string
    {
        return \Illuminate\Support\Str::markdown($markdown);
    }

    /**
     * Query Google Gemini API with fallback models.
     */
    protected function queryGemini(string $prompt, ?string $fileBase64 = null, ?string $rawText = null): string
    {
        if (empty($this->apiKey)) {
            throw new Exception("Gemini API key is not configured.");
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
                            'temperature' => 0.15,
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

        throw new Exception("All Gemini models failed for textbook extraction. " . $lastError);
    }
}
