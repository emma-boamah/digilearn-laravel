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
use App\Services\TextbookLocalParser;
use App\Services\PedagogicalCourseSchemeService;
use Exception;

class TextbookExtractionService
{
    protected string $apiKey;
    protected string $model;
    protected TextbookLocalParser $localParser;

    public function __construct(
        TextbookLocalParser $localParser,
        protected PedagogicalCourseSchemeService $schemeService
    ) {
        $this->apiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));
        $this->model = config('services.gemini.model', 'gemini-flash-latest');
        $this->localParser = $localParser;
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
                // Extract first 50 pages which typically contain the TOC
                @exec("pdftotext -f 1 -l 50 -layout {$escaped} -", $output, $returnVar);
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

            $textbook->update([
                'toc_extraction_status' => 'processing',
                'extraction_error' => null,
            ]);

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
                    'extraction_method' => 'ai',
                    'extraction_quality' => 'high',
                    'requires_review' => true,
                    'raw_toc_json' => $data,
                    'extraction_error' => null,
                ]);
            });

            Log::info("Textbook TOC extraction completed successfully for ID {$textbook->id}");
            return $data;

        } catch (\Throwable $e) {
            Log::warning("Textbook TOC AI extraction failed for ID {$textbook->id} ({$e->getMessage()}). Attempting local deterministic fallback parser.");

            try {
                return $this->localParser->parseTableOfContents($textbook);
            } catch (\Throwable $localEx) {
                $formattedError = $this->parseGeminiError($e->getMessage());
                Log::error("Textbook local TOC extraction ALSO failed for ID {$textbook->id}: " . $localEx->getMessage());
                $textbook->update([
                    'toc_extraction_status' => 'failed',
                    'extraction_method' => 'local',
                    'extraction_error' => json_encode($formattedError) . " | Local parser: " . $localEx->getMessage(),
                    'requires_review' => true,
                ]);
                throw $localEx;
            }
        }
    }

    /**
     * Extract full teaching content for a specific chapter or section.
     */
    public function extractChapterContent(TextbookChapter $chapter, ?string $scheme = null): void
    {
        $textbook = $chapter->textbook;
        $subjectName = $textbook->subject?->name ?? 'Mathematics';

        if (empty($scheme)) {
            $scheme = PedagogicalCourseSchemeService::getDefaultSchemeForSubject($subjectName);
        }

        // If chapter already has sections (standard flow from approved TOC), generate each section with pedagogical scheme
        if ($chapter->sections()->exists()) {
            foreach ($chapter->sections as $section) {
                $this->generateSectionContent($section, $scheme);
            }

            $chapter->textbook->update([
                'content_extraction_status' => 'extracted',
                'extraction_method' => 'pedagogical_engine',
                'requires_review' => true,
            ]);
            return;
        }

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

        try {
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

            $chapter->textbook->update([
                'content_extraction_status' => 'extracted',
                'extraction_method' => 'ai',
                'requires_review' => true,
            ]);

        } catch (\Throwable $e) {
            Log::warning("Textbook chapter content AI extraction failed for Chapter ID {$chapter->id} ({$e->getMessage()}). Falling back to local content extractor.");
            try {
                $this->localParser->parseChapterContent($chapter);
            } catch (\Throwable $localEx) {
                Log::error("Textbook local chapter content extraction ALSO failed for Chapter ID {$chapter->id}: " . $localEx->getMessage());
                throw $localEx;
            }
        }
    }

    /**
     * Helper to render markdown string to HTML while preserving SVG graphics.
     */
    protected function renderMarkdownToHtml(string $markdown): string
    {
        $svgMap = [];
        $counter = 0;
        $safeMarkdown = preg_replace_callback('/<svg\b[^>]*>[\s\S]*?<\/svg>/i', function ($m) use (&$svgMap, &$counter) {
            $token = "<!--SVG_BLOCK_PLACEHOLDER_{$counter}-->";
            $counter++;
            $svgMap[$token] = $m[0];
            return "\n\n{$token}\n\n";
        }, $markdown);

        $html = \Illuminate\Support\Str::markdown($safeMarkdown, [
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
        ]);

        foreach ($svgMap as $token => $svg) {
            $html = str_replace("<p>{$token}</p>", $svg, $html);
            $html = str_replace($token, $svg, $html);
        }

        return $html;
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
            'gemini-3.6-flash',
            'gemini-3.5-flash',
            'gemini-flash-latest',
            'gemini-3.1-flash-lite',
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

    /**
     * Parse raw exception or API error into human-readable diagnosis and actionable guidance.
     */
    public function parseGeminiError(string $rawError): array
    {
        $error = [
            'type' => 'unknown',
            'title' => 'Extraction Failed',
            'message' => 'An unexpected error occurred during AI analysis.',
            'action' => null,
            'action_label' => null,
            'raw' => $rawError,
        ];

        // 1. Credit / Prepayment depletion or Resource Exhausted (HTTP 429)
        if (
            str_contains($rawError, 'RESOURCE_EXHAUSTED') ||
            str_contains($rawError, 'prepayment credits are depleted') ||
            str_contains($rawError, 'billing#prepay') ||
            str_contains($rawError, 'quota') ||
            str_contains($rawError, 'HTTP 429')
        ) {
            $error['type'] = 'token_exhausted';
            $error['title'] = 'Gemini Credits Depleted (HTTP 429)';
            $error['message'] = 'Google Gemini credits are depleted. Please top up in Google AI Studio.';
            $error['action'] = 'https://ai.studio/projects';
            $error['action_label'] = 'Manage Gemini Billing in AI Studio';
            return $error;
        }

        // 2. Authentication / Invalid API Key (HTTP 400 / 401 / 403)
        if (
            str_contains($rawError, 'API_KEY_INVALID') ||
            str_contains($rawError, 'API key not valid') ||
            str_contains($rawError, 'HTTP 401') ||
            str_contains($rawError, 'HTTP 403') ||
            str_contains($rawError, 'PERMISSION_DENIED')
        ) {
            $error['type'] = 'auth_error';
            $error['title'] = 'Gemini API Key Invalid or Unauthorized';
            $error['message'] = 'The configured Gemini API key was rejected by Google AI. Please check your GEMINI_API_KEY environment variable.';
            $error['action'] = 'https://aistudio.google.com/app/apikey';
            $error['action_label'] = 'Get API Key from AI Studio';
            return $error;
        }

        // 3. File not found
        if (str_contains($rawError, 'PDF file not found')) {
            $error['type'] = 'file_missing';
            $error['title'] = 'PDF File Missing from Storage';
            $error['message'] = 'The textbook PDF document could not be located on the server filesystem. Please try re-uploading the file.';
            return $error;
        }

        // 4. JSON parse error / invalid structure returned
        if (str_contains($rawError, 'did not return valid chapters list')) {
            $error['type'] = 'json_parse_error';
            $error['title'] = 'Table of Contents Formatting Issue';
            $error['message'] = 'The AI read the document but was unable to detect standard chapter divisions in the first 25 pages. You can retry the extraction or verify the PDF layout.';
            return $error;
        }

        // 5. Network / timeout
        if (str_contains($rawError, 'cURL error') || str_contains($rawError, 'timed out') || str_contains($rawError, 'timeout')) {
            $error['type'] = 'timeout';
            $error['title'] = 'Connection Timeout';
            $error['message'] = 'The request to Google AI timed out while uploading or processing the large document. Please try again.';
            return $error;
        }

        return $error;
    }

    /**
     * Extract page text context from textbook PDF for a given page range.
     */
    public function extractPageContext(Textbook $textbook, ?int $start, ?int $end): string
    {
        if (!$start || !function_exists('exec')) {
            return '';
        }

        $fullPath = Storage::disk('public')->path($textbook->file_path);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/public/' . $textbook->file_path);
        }

        if (!file_exists($fullPath)) {
            return '';
        }

        $output = [];
        $returnVar = 0;
        $startPage = (int) $start;
        $endPage = $end ? (int) $end : ($startPage + 10);
        $escaped = escapeshellarg($fullPath);
        @exec("pdftotext -f {$startPage} -l {$endPage} -layout {$escaped} - 2>/dev/null", $output, $returnVar);

        return ($returnVar === 0 && !empty($output)) ? implode("\n", $output) : '';
    }

    /**
     * Generate course reading text for a specific section using the Pedagogical Scheme Engine.
     */
    public function generateSectionContent(TextbookSection $section, ?string $scheme = null): array
    {
        $chapter = $section->chapter;
        $textbook = $chapter->textbook;
        $subjectName = $textbook->subject?->name ?? 'Mathematics';
        $gradeLevel = $textbook->level?->name ?? 'JHS';

        if (empty($scheme)) {
            $scheme = $section->pedagogical_scheme ?: PedagogicalCourseSchemeService::getDefaultSchemeForSubject($subjectName);
        }

        $context = $this->extractPageContext(
            $textbook,
            $section->page_start ?: $chapter->page_start,
            $section->page_end ?: $chapter->page_end
        );

        $result = $this->schemeService->generateContent(
            topicTitle: $section->title,
            subjectName: $subjectName,
            gradeLevel: $gradeLevel,
            contextText: $context,
            scheme: $scheme
        );

        $section->update([
            'content_markdown' => $result['markdown'],
            'content_html' => $result['html'],
            'key_takeaways' => $result['key_takeaways'],
            'lesson_quiz' => $result['quiz'],
            'media_resources' => $result['media_resources'] ?? [],
            'pedagogical_scheme' => $scheme,
            'is_published' => true,
        ]);

        return $result;
    }
}
