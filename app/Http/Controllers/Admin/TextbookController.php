<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Textbook;
use App\Models\TextbookChapter;
use App\Models\TextbookSection;
use App\Models\Curriculum;
use App\Models\CurriculumIndicator;
use App\Models\Subject;
use App\Models\Level;
use App\Models\LevelGroup;
use App\Jobs\ProcessTextbookTocJob;
use App\Jobs\ProcessTextbookContentJob;
use App\Services\TextbookExtractionService;
use App\Services\PedagogicalCourseSchemeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TextbookController extends Controller
{
    /**
     * Display a listing of textbooks.
     */
    public function index(Request $request)
    {
        $query = Textbook::with(['subject', 'level', 'levelGroup', 'curriculum', 'uploader'])
            ->withCount('chapters');

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('curriculum_id')) {
            $query->where('curriculum_id', $request->curriculum_id);
        }

        $textbooks = $query->latest()->paginate(15);
        $subjects = Subject::orderBy('name')->get();
        $levels = Level::orderBy('rank')->get();
        $curricula = Curriculum::where('is_approved', true)->orderBy('title')->get();

        return view('admin.textbooks.index', compact('textbooks', 'subjects', 'levels', 'curricula'));
    }

    /**
     * Store a newly uploaded textbook PDF.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:100',
            'subject_id' => 'required|exists:subjects,id',
            'level_id' => 'nullable|exists:levels,id',
            'curriculum_id' => 'nullable|exists:curricula,id',
            'textbook_file' => 'required|file|mimes:pdf|max:102400', // max 100MB
        ]);

        $file = $request->file('textbook_file');
        $fileName = 'textbook_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('textbooks', $fileName, 'public');

        $textbook = Textbook::create([
            'title' => $request->title,
            'author' => $request->author,
            'publisher' => $request->publisher,
            'edition' => $request->edition,
            'subject_id' => $request->subject_id,
            'level_id' => $request->level_id,
            'curriculum_id' => $request->curriculum_id,
            'file_path' => $filePath,
            'file_size_bytes' => $file->getSize(),
            'toc_extraction_status' => 'pending',
            'uploaded_by' => Auth::id(),
        ]);

        // Dispatch background TOC extraction job
        ProcessTextbookTocJob::dispatch($textbook->id);

        return redirect()->route('admin.textbooks.show', $textbook)
            ->with('success', 'Textbook uploaded! Table of Contents extraction has begun.');
    }

    /**
     * Display the specified textbook for reviewing TOC and content.
     */
    public function show(Textbook $textbook)
    {
        $textbook->load([
            'subject',
            'level',
            'levelGroup',
            'curriculum.strands.subStrands.indicators',
            'chapters.sections.curriculumIndicator',
            'chapters.sections.media',
        ]);

        $availableIndicators = collect();
        if ($textbook->curriculum) {
            $availableIndicators = CurriculumIndicator::whereHas('subStrand.strand', function ($q) use ($textbook) {
                $q->where('curriculum_id', $textbook->curriculum_id);
            })->get();
        }

        $chaptersData = $textbook->chapters->map(function ($c) {
            return [
                'id' => $c->id,
                'title' => $c->title,
                'page_start' => $c->page_start,
                'page_end' => $c->page_end,
                'sort_order' => $c->sort_order,
                'isOpen' => true,
                'isEditing' => false,
                'editTitle' => $c->title,
                'editPageStart' => $c->page_start,
                'editPageEnd' => $c->page_end,
                'newTitle' => '',
                'newPageStart' => '',
                'newPageEnd' => '',
                'isSavingQuick' => false,
                'sections' => $c->sections->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'chapter_id' => $s->chapter_id,
                        'title' => $s->title,
                        'page_start' => $s->page_start,
                        'page_end' => $s->page_end,
                        'sort_order' => $s->sort_order,
                        'curriculum_indicator_id' => $s->curriculum_indicator_id,
                        'has_content' => !empty($s->content_markdown) || !empty($s->content_html),
                        'pedagogical_scheme' => $s->pedagogical_scheme,
                        'is_published' => (bool) $s->is_published,
                        'isEditing' => false,
                        'editTitle' => $s->title,
                        'editPageStart' => $s->page_start,
                        'editPageEnd' => $s->page_end,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        $indicatorsData = $availableIndicators->map(function ($ind) {
            return [
                'id' => $ind->id,
                'code' => $ind->indicator_code ?? 'IND',
                'title' => \Illuminate\Support\Str::limit($ind->title, 40),
            ];
        })->values()->all();

        return view('admin.textbooks.show', compact('textbook', 'availableIndicators', 'chaptersData', 'indicatorsData'));
    }

    /**
     * Approve the Table of Contents.
     */
    public function approveToc(Textbook $textbook, Request $request)
    {
        $textbook->update(['is_toc_approved' => true]);

        if ($request->boolean('start_content_extraction')) {
            ProcessTextbookContentJob::dispatch($textbook->id);
            return back()->with('success', 'TOC approved! Full content extraction has been queued.');
        }

        return back()->with('success', 'Table of Contents approved successfully.');
    }

    /**
     * Set or toggle this textbook as the active course for students on Guided Learning.
     */
    public function toggleCourseActive(Textbook $textbook, Request $request)
    {
        $newStatus = $request->has('is_course_active')
            ? $request->boolean('is_course_active')
            : !$textbook->is_course_active;

        if ($newStatus) {
            // Deactivate any other textbook for the same subject and level/group
            $query = Textbook::where('subject_id', $textbook->subject_id)
                ->where('id', '!=', $textbook->id);

            if ($textbook->level_id) {
                $query->where(function ($q) use ($textbook) {
                    $q->where('level_id', $textbook->level_id)
                      ->orWhereNull('level_id');
                });
            }

            $query->update(['is_course_active' => false]);
            $textbook->update(['is_course_active' => true]);

            $subjectName = $textbook->subject ? $textbook->subject->name : 'its subject';
            $gradeName = $textbook->level ? $textbook->level->title : ($textbook->levelGroup ? $textbook->levelGroup->title : 'All Grades');
            $message = "\"{$textbook->title}\" is now set as the Active Student Course for {$subjectName} ({$gradeName}).";
        } else {
            $gradeName = $textbook->level ? $textbook->level->title : ($textbook->levelGroup ? $textbook->levelGroup->title : 'All Grades');
            $textbook->update(['is_course_active' => false]);
            $message = "\"{$textbook->title}\" is no longer the active student course for ({$gradeName}).";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_course_active' => $textbook->is_course_active,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Retry Table of Contents extraction for a failed or pending textbook.
     */
    public function retryToc(Textbook $textbook)
    {
        $textbook->update([
            'toc_extraction_status' => 'pending',
            'extraction_error' => null,
        ]);

        ProcessTextbookTocJob::dispatch($textbook->id);

        return back()->with('success', 'TOC extraction job has been queued. If background workers are running, it will extract automatically.');
    }

    /**
     * Get live extraction status and error details for a textbook (AJAX polling).
     */
    public function status(Textbook $textbook): JsonResponse
    {
        $errorData = null;
        if (!empty($textbook->extraction_error)) {
            $errorData = is_array($textbook->extraction_error)
                ? $textbook->extraction_error
                : json_decode($textbook->extraction_error, true);
        }

        return response()->json([
            'id' => $textbook->id,
            'toc_extraction_status' => $textbook->toc_extraction_status,
            'content_extraction_status' => $textbook->content_extraction_status,
            'is_toc_approved' => (bool) $textbook->is_toc_approved,
            'chapters_count' => $textbook->chapters()->count(),
            'error' => $errorData,
        ]);
    }

    /**
     * Queue content extraction for a textbook or single chapter.
     */
    public function extractContent(Textbook $textbook, Request $request)
    {
        $chapterId = $request->input('chapter_id');
        ProcessTextbookContentJob::dispatch($textbook->id, $chapterId);

        return back()->with('success', 'Content extraction job queued.');
    }

    /**
     * Link a textbook section to a curriculum indicator.
     */
    public function linkIndicator(Request $request, TextbookSection $section): JsonResponse
    {
        $validated = $request->validate([
            'curriculum_indicator_id' => 'nullable|exists:curriculum_indicators,id',
        ]);

        $section->update($validated);

        return response()->json(['success' => true, 'section' => $section]);
    }

    /**
     * AJAX update for a textbook chapter.
     */
    public function updateChapter(Request $request, TextbookChapter $chapter): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'page_start' => 'nullable|integer',
            'page_end' => 'nullable|integer',
        ]);

        $chapter->update($validated);

        return response()->json(['success' => true, 'chapter' => $chapter]);
    }

    /**
     * AJAX update for a textbook section.
     */
    public function updateSection(Request $request, TextbookSection $section): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'page_start' => 'nullable|integer',
            'page_end' => 'nullable|integer',
            'content_markdown' => 'nullable|string',
            'content_html' => 'nullable|string',
        ]);

        if (!empty($validated['content_markdown']) && empty($validated['content_html'])) {
            $validated['content_html'] = \Illuminate\Support\Str::markdown($validated['content_markdown']);
        }

        $section->update($validated);

        return response()->json(['success' => true, 'section' => $section]);
    }

    /**
     * Delete a textbook.
     */
    public function destroy(Textbook $textbook)
    {
        if ($textbook->file_path && Storage::disk('public')->exists($textbook->file_path)) {
            Storage::disk('public')->delete($textbook->file_path);
        }

        $textbook->delete();

        return redirect()->route('admin.textbooks.index')
            ->with('success', 'Textbook deleted successfully.');
    }

    /**
     * AJAX: Create a new chapter under a textbook.
     */
    public function storeChapter(Request $request, Textbook $textbook): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'page_start' => 'nullable|integer|min:1',
            'page_end' => 'nullable|integer|min:1',
        ]);

        $maxOrder = $textbook->chapters()->max('sort_order') ?? 0;

        $chapter = $textbook->chapters()->create([
            'title' => $validated['title'],
            'page_start' => $validated['page_start'] ?? null,
            'page_end' => $validated['page_end'] ?? null,
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json(['success' => true, 'chapter' => $chapter->load('sections')]);
    }

    /**
     * AJAX: Create a new section under a chapter.
     */
    public function storeSection(Request $request, TextbookChapter $chapter): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'page_start' => 'nullable|integer|min:1',
            'page_end' => 'nullable|integer|min:1',
        ]);

        $maxOrder = $chapter->sections()->max('sort_order') ?? 0;

        $section = $chapter->sections()->create([
            'title' => $validated['title'],
            'page_start' => $validated['page_start'] ?? null,
            'page_end' => $validated['page_end'] ?? null,
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json(['success' => true, 'section' => $section]);
    }

    /**
     * AJAX: Delete a chapter and all its sections.
     */
    public function destroyChapter(TextbookChapter $chapter): JsonResponse
    {
        $chapter->sections()->delete();
        $chapter->delete();

        return response()->json(['success' => true]);
    }

    /**
     * AJAX: Bulk create chapters under a textbook.
     */
    public function bulkStoreChapters(Request $request, Textbook $textbook): JsonResponse
    {
        $validated = $request->validate([
            'chapters' => 'required|array|min:1',
            'chapters.*.title' => 'required|string|max:255',
            'chapters.*.page_start' => 'nullable|integer|min:1',
            'chapters.*.page_end' => 'nullable|integer|min:1',
            'chapters.*.sections' => 'nullable|array',
            'chapters.*.sections.*.title' => 'required|string|max:255',
            'chapters.*.sections.*.page_start' => 'nullable|integer|min:1',
            'chapters.*.sections.*.page_end' => 'nullable|integer|min:1',
        ]);

        $maxOrder = $textbook->chapters()->max('sort_order') ?? 0;
        $createdChapters = [];
        $totalSectionsCreated = 0;

        DB::transaction(function () use ($validated, $textbook, &$maxOrder, &$createdChapters, &$totalSectionsCreated) {
            foreach ($validated['chapters'] as $item) {
                $maxOrder++;
                $chapter = $textbook->chapters()->create([
                    'title' => trim($item['title']),
                    'page_start' => $item['page_start'] ?? null,
                    'page_end' => $item['page_end'] ?? null,
                    'sort_order' => $maxOrder,
                ]);

                $sectionsList = collect();
                if (!empty($item['sections']) && is_array($item['sections'])) {
                    $secOrder = 0;
                    foreach ($item['sections'] as $sItem) {
                        $sTitle = trim($sItem['title'] ?? '');
                        if (!$sTitle) continue;
                        $secOrder++;
                        $sec = $chapter->sections()->create([
                            'title' => $sTitle,
                            'page_start' => $sItem['page_start'] ?? null,
                            'page_end' => $sItem['page_end'] ?? null,
                            'sort_order' => $secOrder,
                            'is_published' => true,
                        ]);
                        $sectionsList->push($sec);
                        $totalSectionsCreated++;
                    }
                }
                $chapter->setRelation('sections', $sectionsList);
                $createdChapters[] = $chapter;
            }
        });

        return response()->json([
            'success' => true,
            'count' => count($createdChapters),
            'sections_count' => $totalSectionsCreated,
            'chapters' => $createdChapters,
        ]);
    }

    /**
     * AJAX: Bulk create sections under a chapter.
     */
    public function bulkStoreSections(Request $request, TextbookChapter $chapter): JsonResponse
    {
        $validated = $request->validate([
            'sections' => 'required|array|min:1',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.page_start' => 'nullable|integer|min:1',
            'sections.*.page_end' => 'nullable|integer|min:1',
        ]);

        $maxOrder = $chapter->sections()->max('sort_order') ?? 0;
        $createdSections = [];

        foreach ($validated['sections'] as $item) {
            $maxOrder++;
            $createdSections[] = $chapter->sections()->create([
                'title' => trim($item['title']),
                'page_start' => $item['page_start'] ?? null,
                'page_end' => $item['page_end'] ?? null,
                'sort_order' => $maxOrder,
            ]);
        }

        return response()->json([
            'success' => true,
            'count' => count($createdSections),
            'sections' => $createdSections,
        ]);
    }

    /**
     * AJAX: Reorder chapters in a textbook.
     */
    public function reorderChapters(Request $request, Textbook $textbook): JsonResponse
    {
        $validated = $request->validate([
            'chapter_ids' => 'required|array',
            'chapter_ids.*' => 'integer|exists:textbook_chapters,id',
        ]);

        foreach ($validated['chapter_ids'] as $index => $chapterId) {
            $textbook->chapters()->where('id', $chapterId)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * AJAX: Reorder sections in a chapter (or move to chapter).
     */
    public function reorderSections(Request $request, TextbookChapter $chapter): JsonResponse
    {
        $validated = $request->validate([
            'section_ids' => 'required|array',
            'section_ids.*' => 'integer|exists:textbook_sections,id',
        ]);

        foreach ($validated['section_ids'] as $index => $sectionId) {
            TextbookSection::where('id', $sectionId)->update([
                'chapter_id' => $chapter->id,
                'sort_order' => $index + 1,
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * AJAX: Get pedagogical content, scheme, takeaways, and quiz for a section.
     */
    public function getSectionContent(TextbookSection $section): JsonResponse
    {
        $section->load('chapter.textbook.subject');
        $defaultScheme = PedagogicalCourseSchemeService::getDefaultSchemeForSubject(
            $section->chapter?->textbook?->subject?->name ?? 'Mathematics'
        );

        return response()->json([
            'success' => true,
            'section' => [
                'id' => $section->id,
                'title' => $section->title,
                'page_start' => $section->page_start,
                'page_end' => $section->page_end,
                'pedagogical_scheme' => $section->pedagogical_scheme ?: $defaultScheme,
                'content_markdown' => $section->content_markdown,
                'content_html' => $section->content_html,
                'key_takeaways' => $section->key_takeaways ?? [],
                'lesson_quiz' => $section->lesson_quiz ?? [],
                'media_resources' => $section->media_resources ?? [],
                'is_published' => (bool) $section->is_published,
            ],
            'available_schemes' => PedagogicalCourseSchemeService::AVAILABLE_SCHEMES,
        ]);
    }

    /**
     * AJAX: Generate reading text for a section using the Pedagogical Course Scheme.
     */
    public function generateSectionContent(Request $request, TextbookSection $section, TextbookExtractionService $extractionService): JsonResponse
    {
        $validated = $request->validate([
            'scheme' => 'nullable|string|in:math_first_principles,science_inquiry,social_studies,language_arts,general_academic',
        ]);

        try {
            $result = $extractionService->generateSectionContent($section, $validated['scheme'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Reading text synthesized using ' . ($result['scheme'] ?? 'pedagogical') . ' scheme.',
                'section' => $section->fresh(),
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to generate section content: " . $e->getMessage(), [
                'section_id' => $section->id,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX: Save custom edits to a section's markdown, takeaways, quiz, and media resources.
     */
    public function saveSectionContent(Request $request, TextbookSection $section): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content_markdown' => 'nullable|string',
            'content_html' => 'nullable|string',
            'key_takeaways' => 'nullable|array',
            'lesson_quiz' => 'nullable|array',
            'media_resources' => 'nullable|array',
            'pedagogical_scheme' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);

        if (!empty($validated['content_markdown']) && empty($validated['content_html'])) {
            $validated['content_html'] = app(\App\Services\PedagogicalCourseSchemeService::class)->renderMarkdownToHtml($validated['content_markdown']);
        }

        $section->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Section reading text, quiz, and media resources updated successfully.',
            'section' => $section->fresh(),
        ]);
    }

    /**
     * AJAX: Suggest educational video resources for a section.
     */
    public function suggestSectionMedia(Request $request, TextbookSection $section, PedagogicalCourseSchemeService $schemeService): JsonResponse
    {
        $chapter = $section->chapter;
        $textbook = $chapter->textbook;
        $subjectName = $textbook->subject?->name ?? 'Mathematics';
        $gradeLevel = $textbook->level?->name ?? 'JHS';

        $suggestions = $schemeService->suggestEducationalVideos($section->title, $subjectName, $gradeLevel);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * AJAX: Parse a YouTube URL and return video ID, title and thumbnail preview.
     */
    public function resolveYoutubeUrl(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|string',
        ]);

        $url = trim($validated['url']);
        $videoId = null;

        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $url, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('/^[\w-]{11}$/', $url)) {
            $videoId = $url;
        }

        if (!$videoId) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid YouTube URL or Video ID.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'media' => [
                'id' => 'yt_' . $videoId,
                'type' => 'youtube',
                'title' => 'YouTube Video Tutorial',
                'channel' => 'YouTube',
                'video_id' => $videoId,
                'url' => 'https://www.youtube.com/watch?v=' . $videoId,
                'thumbnail_url' => 'https://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg',
            ]
        ]);
    }

    /**
     * AJAX: Batch generate reading texts for all sections in a chapter.
     */
    public function batchGenerateChapterContent(Request $request, TextbookChapter $chapter, TextbookExtractionService $extractionService): JsonResponse
    {
        $validated = $request->validate([
            'scheme' => 'nullable|string|in:math_first_principles,science_inquiry,social_studies,language_arts,general_academic',
        ]);

        try {
            $extractionService->extractChapterContent($chapter, $validated['scheme'] ?? null);

            return response()->json([
                'success' => true,
                'message' => "Successfully generated reading texts for chapter: {$chapter->title}.",
                'chapter' => $chapter->fresh(['sections']),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Batch generation failed for chapter {$chapter->id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Batch generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
