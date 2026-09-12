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

        return view('admin.textbooks.show', compact('textbook', 'availableIndicators'));
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
}
