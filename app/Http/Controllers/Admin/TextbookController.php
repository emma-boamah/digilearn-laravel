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
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TextbookController extends Controller
{
    /**
     * Display a listing of textbooks.
     */
    public function index(Request $request)
    {
        $query = Textbook::with(['subject', 'level', 'curriculum', 'uploader'])
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
                        'has_content' => !empty($s->content_markdown),
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
        ]);

        $maxOrder = $textbook->chapters()->max('sort_order') ?? 0;
        $createdChapters = [];

        foreach ($validated['chapters'] as $item) {
            $maxOrder++;
            $created = $textbook->chapters()->create([
                'title' => trim($item['title']),
                'page_start' => $item['page_start'] ?? null,
                'page_end' => $item['page_end'] ?? null,
                'sort_order' => $maxOrder,
            ]);
            $created->setRelation('sections', collect());
            $createdChapters[] = $created;
        }

        return response()->json([
            'success' => true,
            'count' => count($createdChapters),
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
}
