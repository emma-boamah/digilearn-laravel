<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PdfQuizParserService
{
    private string $geminiApiKey;
    private string $geminiModel;

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));
        $this->geminiModel = config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Extract questions from a PDF file or text content into structured Quiz data.
     *
     * @param string $filePath Absolute path to PDF file
     * @param string|null $topic Topic title fallback
     * @param string|null $gradeLevel Grade level
     * @return array Array containing quiz structure and questions
     */
    public function parsePdfToQuiz(string $filePath, ?string $topic = 'Parsed Quiz Paper', ?string $gradeLevel = 'General'): array
    {
        $extractedText = $this->extractTextFromPdf($filePath);

        if (empty($extractedText)) {
            // Fallback: read raw file contents if readable or return default error
            $extractedText = @file_get_contents($filePath);
            if (empty($extractedText)) {
                throw new Exception('Could not extract readable text from the provided PDF file.');
            }
        }

        return $this->parseTextToQuizStructure($extractedText, $topic, $gradeLevel);
    }

    /**
     * Basic PDF text extraction using system pdftotext tool if available or regex parsing.
     */
    private function extractTextFromPdf(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return '';
        }

        // Try pdftotext CLI command if available on OS
        if (function_exists('exec')) {
            $output = [];
            $returnVar = 0;
            $escapedPath = escapeshellarg($filePath);
            @exec("pdftotext {$escapedPath} -", $output, $returnVar);
            if ($returnVar === 0 && !empty($output)) {
                return implode("\n", $output);
            }
        }

        // Simple raw stream text extraction fallback
        $content = @file_get_contents($filePath);
        if (!$content) {
            return '';
        }

        // Basic PDF stream objects regex extractor
        preg_match_all('/Stream[\r\n]+(.*?)[\r\n]+endstream/is', $content, $matches);
        $text = '';
        foreach ($matches[1] as $stream) {
            $gz = @gzuncompress($stream);
            if ($gz !== false) {
                $text .= ' ' . preg_replace('/[^\x20-\x7E\s]/', '', $gz);
            } else {
                $text .= ' ' . preg_replace('/[^\x20-\x7E\s]/', '', $stream);
            }
        }

        return trim($text);
    }

    /**
     * Pass extracted text to Gemini AI to structure into questions (MCQs & Essay).
     */
    public function parseTextToQuizStructure(string $text, ?string $topic = 'Quiz', ?string $gradeLevel = 'General'): array
    {
        if (empty($this->geminiApiKey)) {
            // Fallback mock structuring if API key not present
            return $this->fallbackParseText($text, $topic, $gradeLevel);
        }

        $prompt = <<<PROMPT
You are an expert exam paper parser. Analyze the following quiz/exam document text and extract all questions.

Document Topic: {$topic}
Grade Level: {$gradeLevel}

Source Text:
---
{$text}
---

Output MUST be valid JSON only with NO markdown code block ticks. Use exact schema:
{
  "title": "Extracted Quiz Title",
  "description": "Brief summary of quiz paper",
  "questions": [
    {
      "question": "Question text here",
      "type": "mcq", // or "essay"
      "options": ["Option A", "Option B", "Option C", "Option D"], // empty for essay
      "correct_answer": "Option A", // or correct answer text / key
      "explanation": "Brief explanation if provided",
      "points": 1
    }
  ]
}
PROMPT;

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->geminiModel}:generateContent?key={$this->geminiApiKey}";

            $response = Http::timeout(45)
		->withOptions([
			'curl' => [
				CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4 // Forces IPv4 to fix location block
			]
		])
		->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'responseMimeType' => 'application/json'
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $cleanJson = trim(str_replace(['```json', '```'], '', $rawText));
                $parsed = json_decode($cleanJson, true);

                if (is_array($parsed) && isset($parsed['questions'])) {
                    return $parsed;
                }
            }

            Log::warning('Gemini PDF quiz parsing response unparseable, falling back to local regex');
            return $this->fallbackParseText($text, $topic, $gradeLevel);

        } catch (Exception $e) {
            Log::error('PDF Quiz Parsing Exception: ' . $e->getMessage());
            return $this->fallbackParseText($text, $topic, $gradeLevel);
        }
    }

    /**
     * Fallback parser when AI API is unavailable.
     */
    private function fallbackParseText(string $text, ?string $topic, ?string $gradeLevel): array
    {
        $lines = explode("\n", $text);
        $questions = [];
        $currentQuestion = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^(\d+[\.\)]|[Qq]uestion\s*\d+[:\.]?)/', $line)) {
                if ($currentQuestion) {
                    $questions[] = $currentQuestion;
                }
                $currentQuestion = [
                    'question' => preg_replace('/^(\d+[\.\)]|[Qq]uestion\s*\d+[:\.]?)\s*/', '', $line),
                    'type' => 'mcq',
                    'options' => [],
                    'correct_answer' => '',
                    'explanation' => '',
                    'points' => 1
                ];
            } elseif ($currentQuestion && preg_match('/^[A-Da-d][\.\)]\s*(.*)/', $line, $optMatch)) {
                $currentQuestion['options'][] = $optMatch[1];
                if (empty($currentQuestion['correct_answer'])) {
                    $currentQuestion['correct_answer'] = $optMatch[1];
                }
            }
        }

        if ($currentQuestion) {
            $questions[] = $currentQuestion;
        }

        if (empty($questions)) {
            $questions[] = [
                'question' => 'Sample Question from PDF: ' . substr($text, 0, 100) . '...',
                'type' => 'essay',
                'options' => [],
                'correct_answer' => 'Answer as written in PDF material.',
                'explanation' => 'Extracted from PDF content.',
                'points' => 1
            ];
        }

        return [
            'title' => $topic ?? 'PDF Extracted Quiz',
            'description' => 'Quiz extracted automatically from PDF document',
            'questions' => $questions
        ];
    }
}
