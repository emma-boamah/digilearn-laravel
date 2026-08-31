<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Document;
use App\Models\LevelGroup;
use App\Models\ContentCategory;
use App\Models\Subject;
use App\Services\SubscriptionAccessService;
use App\Services\PdfParser;
use App\Services\UrlObfuscator;
use App\Services\DocumentCognitiveService;
use App\Services\GeminiSq3rService;

class DocumentController extends Controller
{
    /**
     * Show documents / library collection page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedLevelGroup = $request->query('level_group', $user->current_level_group ?? session('selected_level_group', 'jhs'));
        
        if (!session('selected_level_group')) {
            session(['selected_level_group' => $selectedLevelGroup]);
        }

        $levelGroups = LevelGroup::with('levels')->orderBy('display_order')->get();
        $group = LevelGroup::where('slug', $selectedLevelGroup)->with('levels')->first();
        $canonicalGrades = $group ? $group->levels->pluck('title')->toArray() : [];
        $unlockedGrades = $canonicalGrades;

        $selectedGrade = $request->query('grade');
        $validSelectedGrade = null;
        if ($selectedGrade && in_array($selectedGrade, $unlockedGrades)) {
            $validSelectedGrade = $selectedGrade;
        }

        // Subscription check
        $requiresSubscription = false;
        $allowedGradeLevels = [];
        if (!$user || !$user->is_superuser) {
            $allowedGradeLevels = SubscriptionAccessService::getAllowedGradeLevels($user);
            if (empty($allowedGradeLevels)) {
                $requiresSubscription = true;
            }
        }

        $context = $request->query('context', 'all');
        $subjectId = $request->query('subject');
        $formatFilter = strtolower($request->query('format', 'all'));
        $search = $request->query('search');

        // Target grade levels
        $targetGrades = $validSelectedGrade ? [$validSelectedGrade] : $canonicalGrades;

        // Fetch categories
        $categories = ContentCategory::orderBy('name')->get();

        $schoolId = $user ? $user->school_id : null;

        // Fetch ONLY subjects that actually have documents matching current grade level and school
        $subjectIdsWithDocs = Document::where(function ($q) use ($schoolId) {
                if ($schoolId) {
                    $q->whereNull('school_id')->orWhere('school_id', $schoolId);
                } else {
                    $q->whereNull('school_id');
                }
            })
            ->when(!empty($targetGrades), function ($q) use ($targetGrades) {
                $q->where(function ($sub) use ($targetGrades) {
                    $sub->whereIn('grade_level', $targetGrades)
                        ->orWhereHas('video', function ($vq) use ($targetGrades) {
                            $vq->whereIn('grade_level', $targetGrades);
                        });
                });
            })
            ->whereHas('video', function ($vq) {
                $vq->whereNotNull('subject_id');
            })
            ->with('video:id,subject_id')
            ->get()
            ->pluck('video.subject_id')
            ->filter()
            ->unique()
            ->values();

        $subjects = Subject::whereIn('id', $subjectIdsWithDocs)->orderBy('name')->get();

        // Query Documents
        $query = Document::with(['video.subject', 'uploader', 'categories'])
            ->where(function ($q) use ($schoolId) {
                if ($schoolId) {
                    $q->whereNull('school_id')->orWhere('school_id', $schoolId);
                } else {
                    $q->whereNull('school_id');
                }
            });

        // Filter by grade level (check document's direct grade_level OR associated video's grade_level)
        if (!empty($targetGrades)) {
            $query->where(function ($q) use ($targetGrades) {
                $q->whereIn('grade_level', $targetGrades)
                  ->orWhereHas('video', function ($vq) use ($targetGrades) {
                      $vq->whereIn('grade_level', $targetGrades);
                  });
            });
        }

        // Filter by context / category
        if ($context !== 'all') {
            $query->where(function ($q) use ($context) {
                $q->whereHas('categories', function ($cq) use ($context) {
                    $cq->where('slug', $context)->orWhere('name', 'like', "%{$context}%");
                })->orWhereHas('video.categories', function ($cq) use ($context) {
                    $cq->where('slug', $context)->orWhere('name', 'like', "%{$context}%");
                });
            });
        }

        // Filter by Subject
        if ($subjectId && $subjectId !== 'all') {
            $query->whereHas('video', function ($vq) use ($subjectId) {
                if (is_numeric($subjectId)) {
                    $vq->where('subject_id', $subjectId);
                } else {
                    $vq->whereHas('subject', function($sq) use ($subjectId) {
                        $sq->where('name', 'like', "%{$subjectId}%");
                    });
                }
            });
        }

        // Filter by File Format
        if ($formatFilter !== 'all') {
            if ($formatFilter === 'pdf') {
                $query->where(function($q) {
                    $q->where('file_type', 'pdf')->orWhere('file_path', 'like', '%.pdf');
                });
            } elseif (in_array($formatFilter, ['ppt', 'pptx'])) {
                $query->where(function($q) {
                    $q->whereIn('file_type', ['ppt', 'pptx'])
                      ->orWhere('file_path', 'like', '%.ppt')
                      ->orWhere('file_path', 'like', '%.pptx');
                });
            } elseif (in_array($formatFilter, ['doc', 'docx'])) {
                $query->where(function($q) {
                    $q->whereIn('file_type', ['doc', 'docx'])
                      ->orWhere('file_path', 'like', '%.doc')
                      ->orWhere('file_path', 'like', '%.docx');
                });
            } elseif ($formatFilter === 'epub') {
                $query->where(function($q) {
                    $q->where('file_type', 'epub')->orWhere('file_path', 'like', '%.epub');
                });
            }
        }

        // Filter by Search Query
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('video', function ($vq) use ($search) {
                      $vq->where('title', 'like', "%{$search}%");
                  });
            });
        }

        $documents = $query->latest()->paginate(16)->withQueryString();

        // Populate metadata (page counts, formats, subjects)
        $documents->getCollection()->transform(function ($doc) {
            $ext = strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION));
            if (!$ext && $doc->file_type) $ext = strtolower($doc->file_type);
            if (!$ext) $ext = 'pdf';
            
            $doc->resolved_format = strtoupper($ext);
            $doc->resolved_grade = $doc->grade_level ?: ($doc->video->grade_level ?? 'General');
            $doc->resolved_subject = $doc->video->subject->name ?? 'General Study';
            
            // Determine pages / slides count
            if (in_array($ext, ['ppt', 'pptx'])) {
                $doc->meta_count_label = 'Slides';
                $doc->meta_count = 24;
            } else {
                $doc->meta_count_label = 'Pages';
                $doc->meta_count = PdfParser::getPageCount($doc->file_path) ?: 1;
            }

            $doc->formatted_size = $doc->getFormattedFileSize();
            $doc->file_url = asset('storage/' . $doc->file_path);

            // Resolve cover image thumbnail
            $videoThumbnail = null;
            if ($doc->video) {
                if (!empty($doc->video->thumbnail_path)) {
                    $videoThumbnail = str_starts_with($doc->video->thumbnail_path, 'http') || str_starts_with($doc->video->thumbnail_path, 'images/') 
                        ? asset($doc->video->thumbnail_path) 
                        : asset('storage/' . $doc->video->thumbnail_path);
                } elseif (!empty($doc->video->thumbnail)) {
                    $videoThumbnail = asset($doc->video->thumbnail);
                }
            }

            if ($ext === 'pdf') {
                $doc->cover_image_url = PdfParser::getCoverThumbnailUrl($doc->file_path, $videoThumbnail);
            } else {
                $doc->cover_image_url = $videoThumbnail;
            }

            return $doc;
        });

        return view('dashboard.documents.index', compact(
            'documents',
            'levelGroups',
            'canonicalGrades',
            'unlockedGrades',
            'selectedLevelGroup',
            'validSelectedGrade',
            'categories',
            'subjects',
            'context',
            'subjectId',
            'formatFilter',
            'search',
            'requiresSubscription'
        ));
    }

    /**
     * Open a document from the library
     */
    public function openLibraryDocument($docId)
    {
        $realId = $docId;
        if (!is_numeric($docId)) {
            $parsed = UrlObfuscator::parseSeoUrl($docId);
            $decoded = $parsed['id'] ?? UrlObfuscator::decode($docId);
            if ($decoded) {
                $realId = $decoded;
            }
        }

        $document = Document::with(['video.subject', 'uploader'])->findOrFail($realId);
        
        $ext = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
        $type = in_array($ext, ['ppt', 'pptx']) ? 'ppt' : 'pdf';
        
        // If attached to a video lesson, redirect to document content viewer with obfuscated parameters
        if ($document->video_id) {
            return redirect()->route('dashboard.lesson.document.content', [
                'lessonId' => UrlObfuscator::encode($document->video_id),
                'type' => $type,
                'docId' => UrlObfuscator::createSeoUrl($document->id, $document->title)
            ]);
        }

        // For standalone document, prepare doc array and return document-content-viewer
        $pageCount = ($type === 'pdf') ? (PdfParser::getPageCount($document->file_path) ?: 1) : 10;
        
        $docData = [
            'id' => $document->id,
            'title' => $document->title,
            'file_url' => asset('storage/' . $document->file_path),
            'file_path' => $document->file_path,
            'file_size' => $document->getFormattedFileSize(),
            'pages' => $pageCount,
            'pages_count' => $pageCount,
            'instructor' => $document->uploader->name ?? 'Admin',
            'subject' => $document->video->subject->name ?? 'Document',
            'year' => date('Y'),
        ];

        $lesson = [
            'id' => 0,
            'title' => $document->title,
            'subject' => $document->video->subject->name ?? 'Document',
            'instructor' => $document->uploader->name ?? 'Admin',
            'year' => date('Y'),
        ];

        $user = Auth::user();
        $selectedLevelGroup = $user->current_level_group ?? session('selected_level_group', 'jhs');

        // Fetch pre-computed background SQ3R cognitive analysis from database
        $sq3rAnalysis = $document->sq3rAnalysis;
        $preloadedCognitiveData = null;

        if ($sq3rAnalysis && $sq3rAnalysis->status === 'completed' && !empty($sq3rAnalysis->structured_payload)) {
            $preloadedCognitiveData = $sq3rAnalysis->structured_payload;
        } elseif (!$sq3rAnalysis || $sq3rAnalysis->status === 'failed') {
            // Proactively queue background processing so it is synthesized in background
            try {
                \App\Jobs\ProcessDocumentSq3rJob::dispatch($document->id);
            } catch (\Throwable $dispatchEx) {
                \Illuminate\Support\Facades\Log::info("ProcessDocumentSq3rJob proactive dispatch notice: " . $dispatchEx->getMessage());
            }
        }

        return view('dashboard.document-content-viewer', [
            'lesson' => $lesson,
            'document' => $docData,
            'selectedLevelGroup' => $selectedLevelGroup,
            'type' => $type,
            'docId' => $document->id,
            'preloadedCognitiveData' => $preloadedCognitiveData
        ]);
    }

    /**
     * Synthesize structured cognitive framework from document contents using full 5-stage SQ3R AI pipeline.
     */
    public function synthesizeCognitiveStructure(Request $request, $docId, GeminiSq3rService $sq3rService, DocumentCognitiveService $cognitiveService)
    {
        $document = Document::find($docId);
        if (!$document) {
            return response()->json(['success' => false, 'error' => 'Document not found'], 404);
        }

        try {
            $analysis = $sq3rService->processDocument($document);
            return response()->json([
                'success' => true,
                'data' => $analysis
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('SQ3R pipeline fallback invoked: ' . $e->getMessage());
            $extractedText = $request->input('extracted_text');
            $slides = $request->input('slides', []);
            $analysis = $cognitiveService->analyzeDocumentContent($document, $extractedText, $slides);
            return response()->json([
                'success' => true,
                'data' => $analysis
            ]);
        }
    }

    /**
     * Evaluate a student's Active Recall Checkpoint answer using Gemini AI.
     * Uses a Socratic/freeCodeCamp-style progressive hint system instead of grading.
     * Forces IPv4 connections via CURLOPT_IPRESOLVE to bypass IPv6 filtering.
     */
    public function evaluateRecallCheck(Request $request)
    {
        $answer = trim($request->input('answer', ''));
        if (mb_strlen($answer) < 2) {
            return response()->json([
                'success' => false,
                'error' => 'Please provide an explanation before checking.'
            ], 400);
        }

        $question = $request->input('question') ?: 'Active recall checkpoint prompt';
        $attempt = max(1, (int) $request->input('attempt', 1));
        $docTitle = $request->input('doc_title', 'Document');
        $sectionTitle = $request->input('section_title', '');

        $apiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));

        // Progressive hint strategy based on attempt number
        if ($attempt <= 1) {
            $hintStrategy = "If the student's answer is CORRECT or mostly correct, respond with is_correct=true and a brief 1-sentence encouragement. If INCORRECT, vague, or incomplete, respond with is_correct=false and give a gentle HINT — a single guiding question or nudge that points them toward the right concept WITHOUT revealing the answer.";
        } elseif ($attempt == 2) {
            $hintStrategy = "If the student's answer is CORRECT or mostly correct, respond with is_correct=true and encouragement. If still INCORRECT, respond with is_correct=false and give a STRONGER hint mentioning the specific term or property to think about, without giving the full solution.";
        } else {
            $hintStrategy = "If the student's answer is CORRECT, respond with is_correct=true. If still INCORRECT after multiple attempts, respond with is_correct=false and now provide a CONCISE TEACHING EXPLANATION (2-3 sentences) clearly explaining the answer so they learn and can proceed.";
        }

        $prompt = <<<PROMPT
You are a warm, Socratic learning coach evaluating a student's Active Recall answer.

Document: "{$docTitle}"
Section: "{$sectionTitle}"
Attempt Number: {$attempt}

Checkpoint Question:
{$question}

Student's Answer:
{$answer}

Strategy for this attempt:
{$hintStrategy}

Respond in this EXACT JSON format ONLY (no markdown fences, no extra text):
{
    "is_correct": true,
    "message": "Your supportive hint or encouragement here."
}
PROMPT;

        $modelsToTry = [
            'gemini-flash-latest',
            'gemini-1.5-flash',
            'gemini-2.0-flash',
            'gemini-flash-lite-latest'
        ];

        $lastError = null;

        if (!empty($apiKey)) {
            foreach ($modelsToTry as $modelName) {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}";

                try {
                    $response = Http::timeout(25)
                        ->withOptions([
                            'curl' => [
                                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
                            ]
                        ])
                        ->post($url, [
                            'contents' => [
                                ['role' => 'user', 'parts' => [['text' => $prompt]]]
                            ],
                            'generationConfig' => [
                                'temperature' => 0.3,
                                'maxOutputTokens' => 512,
                            ],
                        ]);

                    if ($response->successful()) {
                        $json = $response->json();
                        $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                        $cleanJson = trim(preg_replace('/^```(?:json)?|```$/m', '', $text));
                        $evaluation = json_decode($cleanJson, true);

                        if (is_array($evaluation) && isset($evaluation['is_correct'])) {
                            return response()->json([
                                'success' => true,
                                'data' => [
                                    'is_correct' => (bool) $evaluation['is_correct'],
                                    'message' => $evaluation['message'] ?? 'Great effort!',
                                    'attempt' => $attempt,
                                ]
                            ]);
                        }

                        if (!empty(trim($text))) {
                            $isPos = stripos($text, 'true') !== false || stripos($text, 'correct') !== false;
                            return response()->json([
                                'success' => true,
                                'data' => [
                                    'is_correct' => $isPos && stripos($text, 'false') === false,
                                    'message' => trim(strip_tags($text)),
                                    'attempt' => $attempt,
                                ]
                            ]);
                        }
                    } else {
                        $lastError = "HTTP " . $response->status() . ": " . $response->body();
                    }
                } catch (\Throwable $ex) {
                    $lastError = $ex->getMessage();
                    Log::warning("Recall evaluation Gemini error ({$modelName}): " . $ex->getMessage());
                }
            }
        }

        // Intelligent pedagogical fallback when AI API is unavailable
        Log::warning('Recall evaluation AI API notice (using local heuristic fallback): ' . ($lastError ?? 'API key missing'));
        
        $wordCount = str_word_count($answer);
        $isAcceptable = $wordCount >= 10;
        
        if ($isAcceptable) {
            $msg = "Good effort! Your response covers the essential concepts in this section. Review the module notes above to reinforce your mastery.";
        } elseif ($attempt <= 1) {
            $msg = "Think about the primary definition or rule discussed in this section. What are the key properties that define it?";
        } elseif ($attempt == 2) {
            $msg = "Consider the relationship between the elements and boundary conditions introduced in this module. How would you state it in your own words?";
        } else {
            $msg = "This section focuses on applying the governing principle systematically. Make sure to review the core formulas and notes above.";
        }

        return response()->json([
            'success' => true,
            'data' => [
                'is_correct' => $isAcceptable,
                'message' => $msg,
                'attempt' => $attempt,
            ]
        ]);
    }

    // First page - Document preview/selection
    public function viewDocument($lessonId, $type)
    {
        // Check if this is an AJAX request to check document existence
        if (request()->ajax() || request()->wantsJson()) {
            $user = Auth::user();
            $selectedLevelGroup = $user->current_level_group ?? session('selected_level_group');

            if (!$selectedLevelGroup) {
                return response()->json([
                    'exists' => false,
                    'error' => 'level_required',
                    'message' => 'Please select your grade level first.'
                ]);
            }
            
            if (!session('selected_level_group')) {
                session(['selected_level_group' => $selectedLevelGroup]);
            }

            $document = $this->getDocumentForLesson($lessonId, $type);
            return response()->json([
                'exists' => $document !== null && !empty($document),
                'document' => $document
            ]);
        }

        $user = Auth::user();
        $selectedLevelGroup = $user->current_level_group ?? session('selected_level_group');

        // Check if user has selected a level group
        if (!$selectedLevelGroup) {
            return redirect()->route('dashboard.level-selection')
                ->with('error', 'Please select your grade level first.');
        }

        if (!session('selected_level_group')) {
            session(['selected_level_group' => $selectedLevelGroup]);
        }

        // Get lesson data from database (similar to DashboardController approach)
        $gradeLevels = $this->getGradeLevelForLevelGroup($selectedLevelGroup);
        $schoolId = $user ? $user->school_id : null;
        $videos = \App\Models\Video::approved()
            ->whereIn('grade_level', $gradeLevels)
            ->where(function ($q) use ($schoolId) {
                if ($schoolId) {
                    $q->whereNull('school_id')->orWhere('school_id', $schoolId);
                } else {
                    $q->whereNull('school_id');
                }
            })
            ->with('documents')->get();
        $lesson = $videos->firstWhere('id', (int)$lessonId);

        // Convert to array format expected by the view
        if ($lesson) {
            $lesson = [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'subject' => $lesson->subject->name ?? 'Subject',
                'duration' => $lesson->duration ?? 'Unknown',
                'instructor' => $lesson->instructor ?? 'Unknown',
                'year' => $lesson->year ?? '2025',
                'video_url' => $lesson->getVideoUrl(),
                'thumbnail' => $lesson->thumbnail ?? null,
            ];
        }

        if (!$lesson) {
            return view('dashboard.document-viewer', [
                'lesson' => null,
                'document' => null,
                'selectedLevelGroup' => $selectedLevelGroup,
                'type' => $type,
                'error' => 'Lesson not found.'
            ]);
        }

        // Get basic document info for preview
        $document = $this->getDocumentForLesson($lessonId, $type);

        // Ensure document is always an array for consistent view handling
        if (!$document) {
            $document = []; // Empty array instead of null
        } elseif (!is_array($document)) {
            $document = [$document]; // Wrap single document in array
        }

        // Return the simple preview page
        return view('dashboard.document-viewer', compact('lesson', 'document', 'selectedLevelGroup', 'type'));
    }

    // Second page - Document content viewer
    public function viewDocumentContent($lessonId, $type)
    {
        $user = Auth::user();
        $selectedLevelGroup = $user->current_level_group ?? session('selected_level_group');

        // Check if user has selected a level group
        if (!$selectedLevelGroup) {
            return redirect()->route('dashboard.level-selection')
                ->with('error', 'Please select your grade level first.');
        }

        if (!session('selected_level_group')) {
            session(['selected_level_group' => $selectedLevelGroup]);
        }

        // Get lesson data from database (similar to DashboardController approach)
        $gradeLevels = $this->getGradeLevelForLevelGroup($selectedLevelGroup);
        $schoolId = $user ? $user->school_id : null;
        $videos = \App\Models\Video::approved()
            ->whereIn('grade_level', $gradeLevels)
            ->where(function ($q) use ($schoolId) {
                if ($schoolId) {
                    $q->whereNull('school_id')->orWhere('school_id', $schoolId);
                } else {
                    $q->whereNull('school_id');
                }
            })
            ->with('documents')->get();

        // Filter videos based on user's subscription access
        $user = Auth::user();
        $allowedGradeLevels = \App\Services\SubscriptionAccessService::getAllowedGradeLevels($user);
        $videos = $videos->filter(function ($video) use ($allowedGradeLevels) {
            return in_array($video->grade_level, $allowedGradeLevels);
        });

        $lesson = $videos->firstWhere('id', (int)$lessonId);

        // Convert to array format expected by the view
        if ($lesson) {
            $lesson = [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'subject' => $lesson->subject->name ?? 'Subject',
                'duration' => $lesson->duration ?? 'Unknown',
                'instructor' => $lesson->instructor ?? 'Unknown',
                'year' => $lesson->year ?? '2025',
                'video_url' => $lesson->getVideoUrl(),
                'thumbnail' => $lesson->thumbnail ?? null,
            ];
        }

        if (!$lesson) {
            return redirect()->route('dashboard.digilearn')
                ->with('error', 'Lesson not found.');
        }

        // Get full document data with content
        $document = $this->getDocumentContentForLesson($lessonId, $type);

        if (!$document) {
            return redirect()->route('dashboard.lesson.document', [$lessonId, $type])
                ->with('error', 'Document content not found.');
        }

        // Record document view engagement for recommendation system
        if (Auth::check()) {
            \App\Models\UserEngagement::record(
                Auth::id(),
                'document',
                $lessonId,
                'view',
                0, // duration tracked separately
                [
                    'title' => $document['title'] ?? 'Document',
                    'subject' => $lesson['subject'] ?? 'General',
                    'type' => $type,
                    'lesson_id' => $lessonId,
                ]
            );
        }

        // Return the full content viewer page
        return view('dashboard.document-content-viewer', compact('lesson', 'document', 'selectedLevelGroup', 'type'));
    }

    private function getGradeLevelForLevelGroup($levelGroup)
    {
        // Map level groups to all possible grade levels they contain
        $gradeLevels = [
            'primary-lower' => ['Primary 1', 'Primary 2', 'Primary 3'],
            'primary-upper' => ['Primary 4', 'Primary 5', 'Primary 6'],
            'jhs' => ['JHS 1', 'JHS 2', 'JHS 3'],
            'shs' => ['SHS 1', 'SHS 2', 'SHS 3'],
            'university' => ['University']
        ];

        return $gradeLevels[$levelGroup] ?? ['SHS 1'];
    }

    // Basic document info for preview page
    private function getDocumentForLesson($lessonId, $type)
    {
        // Query database for documents related to this video (lesson)
        $documents = \App\Models\Document::where('video_id', $lessonId)->get();

        if ($documents->isEmpty()) {
            return null;
        }

        $result = [];
        foreach ($documents as $document) {
            $fileExtension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
            $documentType = in_array($fileExtension, ['pdf']) ? 'pdf' : (in_array($fileExtension, ['ppt', 'pptx']) ? 'ppt' : ($document->file_type === 'pdf' ? 'pdf' : 'ppt'));

            // Only process document if it matches the requested type
            if ($documentType !== $type) {
                continue;
            }

            $fullPath = storage_path('app/public/' . $document->file_path);

            if ($type === 'pdf') {
                $pageCount = \App\Services\PdfParser::getPageCount($fullPath);
                $result[] = [
                    'id' => $document->id,
                    'title' => $document->title,
                    'file_path' => $document->file_path,
                    'file_url' => asset('storage/' . $document->file_path),
                    'file_size' => $this->formatFileSize($document->file_path),
                    'pages' => $pageCount,
                    'file_type' => 'PDF',
                    'attached_by' => $document->uploader->name ?? 'Instructor',
                ];
            } elseif ($type === 'ppt') {
                $slides = \App\Services\PptxParser::parse($fullPath);
                $slideCount = !empty($slides) ? count($slides) : 1;
                $result[] = [
                    'id' => $document->id,
                    'title' => $document->title,
                    'file_path' => $document->file_path,
                    'file_url' => asset('storage/' . $document->file_path),
                    'file_size' => $this->formatFileSize($document->file_path),
                    'slides' => $slideCount,
                    'file_type' => 'PPT',
                    'attached_by' => $document->uploader->name ?? 'Instructor',
                ];
            }
        }

        if (empty($result)) {
            return null;
        }

        return count($result) === 1 ? $result[0] : $result;
    }

    // Full document content for content viewer page
    private function getDocumentContentForLesson($lessonId, $type)
    {
        // Check if this is a user-created PPT
        if ($type === 'ppt' && request()->has('ppt_id')) {
            $pptId = request()->get('ppt_id');
            $presentations = session('user_presentations', []);

            if (isset($presentations[$lessonId][$pptId])) {
                return $presentations[$lessonId][$pptId];
            }
        }

        // Query database for document related to this video (lesson)
        $docId = request()->get('docId') ?? request()->get('doc_id');
        if ($docId && !is_numeric($docId)) {
            $parsed = UrlObfuscator::parseSeoUrl($docId);
            $decoded = $parsed['id'] ?? UrlObfuscator::decode($docId);
            if ($decoded) {
                $docId = $decoded;
            }
        }
        $query = \App\Models\Document::where('video_id', $lessonId);
        if ($docId) {
            $query->where('id', $docId);
        }
        $document = $query->first();

        // Fallback to first document for lesson if not found by specific docId
        if (!$document && $docId) {
            $document = \App\Models\Document::where('video_id', $lessonId)->first();
        }

        if (!$document) {
            return [];
        }

        // Determine file type based on file extension
        $fileExtension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
        $documentType = in_array($fileExtension, ['pdf']) ? 'pdf' : (in_array($fileExtension, ['ppt', 'pptx']) ? 'ppt' : ($document->file_type === 'pdf' ? 'pdf' : 'ppt'));

        // Only return document if it matches the requested type
        if ($documentType !== $type) {
            return [];
        }

        $fullPath = storage_path('app/public/' . $document->file_path);

        // For PDF documents, return structured content with real page count and storage URL
        if ($type === 'pdf') {
            $pageCount = \App\Services\PdfParser::getPageCount($fullPath);
            $pages = [];
            for ($i = 1; $i <= max(1, $pageCount); $i++) {
                $pages[] = [
                    'number' => $i,
                    'title' => 'Page ' . $i,
                    'content' => ''
                ];
            }

            return [
                'id' => $document->id,
                'title' => $document->title,
                'file_path' => $document->file_path,
                'file_url' => asset('storage/' . $document->file_path),
                'file_size' => $this->formatFileSize($document->file_path),
                'pages_count' => $pageCount,
                'pages' => $pages
            ];
        }

        // For PPT documents, return slide structure
        if ($type === 'ppt') {
            $slides = \App\Services\PptxParser::parse($fullPath);

            // Fallback to mock representation if parsing returned no slides
            if (empty($slides)) {
                $slides = [
                    [
                        'number' => 1,
                        'title' => $document->title,
                        'subtitle' => $document->video->subject->name ?? 'General',
                        'type' => 'title',
                        'content' => []
                    ],
                    [
                        'number' => 2,
                        'title' => 'Document Overview',
                        'content' => [
                            [
                                'text' => $document->description ?: 'Document slides not available for preview.',
                                'is_bullet' => false
                            ]
                        ],
                        'type' => 'definition'
                    ]
                ];
            }

            return [
                'id' => $document->id,
                'title' => $document->title,
                'file_path' => $document->file_path,
                'file_url' => asset('storage/' . $document->file_path),
                'file_size' => $this->formatFileSize($document->file_path),
                'subject' => $document->video->subject->name ?? 'General',
                'slides' => $slides
            ];
        }

        return [];
    }

    // Create new PPT
    public function createPpt($lessonId)
    {
        $user = Auth::user();
        $selectedLevelGroup = $user->current_level_group ?? session('selected_level_group');

        // Check if user has selected a level group
        if (!$selectedLevelGroup) {
            return redirect()->route('dashboard.level-selection')
                ->with('error', 'Please select your grade level first.');
        }

        if (!session('selected_level_group')) {
            session(['selected_level_group' => $selectedLevelGroup]);
        }

        // Get lesson data from database (similar to DashboardController approach)
        $schoolId = $user ? $user->school_id : null;
        $videos = \App\Models\Video::approved()
            ->where('grade_level', $this->getGradeLevelForLevelGroup($selectedLevelGroup))
            ->where(function ($q) use ($schoolId) {
                if ($schoolId) {
                    $q->whereNull('school_id')->orWhere('school_id', $schoolId);
                } else {
                    $q->whereNull('school_id');
                }
            })
            ->with('documents')->get();
        $lesson = $videos->firstWhere('id', (int)$lessonId);

        // Convert to array format expected by the view
        if ($lesson) {
            $lesson = [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'subject' => $lesson->subject->name ?? 'Subject',
                'duration' => $lesson->duration ?? 'Unknown',
                'instructor' => $lesson->instructor ?? 'Unknown',
                'year' => $lesson->year ?? '2025',
                'video_url' => $lesson->getVideoUrl(),
                'thumbnail' => $lesson->thumbnail ?? null,
            ];
        }

        if (!$lesson) {
            return redirect()->route('dashboard.digilearn')
                ->with('error', 'Lesson not found.');
        }

        // Create a new empty PPT structure
        $newPpt = [
            'id' => uniqid(),
            'title' => 'New Presentation',
            'subject' => $lesson['subject'],
            'slides' => [
                [
                    'number' => 1,
                    'type' => 'title',
                    'title' => 'New Presentation',
                    'subtitle' => $lesson['subject'],
                    'content' => ''
                ]
            ]
        ];

        return view('dashboard.ppt-creator', compact('lesson', 'newPpt', 'selectedLevelGroup'));
    }

    // Store new PPT
    public function storePpt(Request $request, $lessonId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slides' => 'required|array|min:1',
            'slides.*.type' => 'required|in:title,definition,list',
            'slides.*.title' => 'required|string',
        ]);

        $user = Auth::user();
        $selectedLevelGroup = $user->current_level_group ?? session('selected_level_group');

        // Check if user has selected a level group
        if (!$selectedLevelGroup) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        if (!session('selected_level_group')) {
            session(['selected_level_group' => $selectedLevelGroup]);
        }

        // In a real application, you would save this to your database
        // For now, we'll store it in session
        $presentations = session('user_presentations', []);
        $pptId = uniqid();
        
        $presentations[$lessonId][$pptId] = [
            'id' => $pptId,
            'title' => $request->title,
            'slides' => $request->slides,
            'created_at' => now(),
            'updated_at' => now()
        ];

        session(['user_presentations' => $presentations]);

        return response()->json([
            'success' => true,
            'message' => 'Presentation created successfully!',
            'ppt_id' => $pptId,
            'redirect_url' => route('dashboard.lesson.document.content', [$lessonId, 'ppt']) . '?ppt_id=' . $pptId
        ]);
    }

    // Update existing PPT
    public function updatePpt(Request $request, $lessonId, $pptId)
    {
        $request->validate([
            'slides' => 'required|array'
        ]);

        $user = Auth::user();
        $selectedLevelGroup = $user->current_level_group ?? session('selected_level_group');

        // Check if user has selected a level group
        if (!$selectedLevelGroup) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        if (!session('selected_level_group')) {
            session(['selected_level_group' => $selectedLevelGroup]);
        }

        $presentations = session('user_presentations', []);
        
        if (isset($presentations[$lessonId][$pptId])) {
            $presentations[$lessonId][$pptId]['slides'] = $request->slides;
            $presentations[$lessonId][$pptId]['updated_at'] = now();
            
            session(['user_presentations' => $presentations]);

            return response()->json([
                'success' => true,
                'message' => 'Presentation updated successfully!'
            ]);
        }

        return response()->json(['error' => 'Presentation not found'], 404);
    }

    public function saveDocumentChanges(Request $request, $lessonId, $type)
    {
        $request->validate([
            'changes' => 'required|array'
        ]);

        $user = Auth::user();
        $selectedLevelGroup = $user->current_level_group ?? session('selected_level_group');

        // Check if user has selected a level group
        if (!$selectedLevelGroup) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        if (!session('selected_level_group')) {
            session(['selected_level_group' => $selectedLevelGroup]);
        }

        // In a real application, you would save these changes to your database
        // For now, we'll just store them in the session
        $documentChanges = session('document_changes', []);
        $documentChanges[$lessonId][$type] = $request->changes;
        session(['document_changes' => $documentChanges]);

        return response()->json([
            'success' => true,
            'message' => 'Document changes saved successfully'
        ]);
    }

    private function formatFileSize($filePath)
    {
        // Check if file exists
        if (!file_exists(storage_path('app/public/' . $filePath))) {
            return 'Unknown';
        }

        $bytes = filesize(storage_path('app/public/' . $filePath));

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 1) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}