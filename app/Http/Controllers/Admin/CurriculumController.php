<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\CurriculumStrand;
use App\Models\CurriculumSubStrand;
use App\Models\CurriculumIndicator;
use App\Models\Subject;
use App\Models\Level;
use App\Models\LevelGroup;
use App\Jobs\ProcessCurriculumExtractionJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CurriculumController extends Controller
{
    /**
     * Display a listing of curricula.
     */
    public function index(Request $request)
    {
        $query = Curriculum::with(['subject', 'level', 'levelGroup', 'uploader'])
            ->withCount('strands');

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('education_body')) {
            $query->where('education_body', $request->education_body);
        }

        if ($request->filled('status')) {
            $query->where('extraction_status', $request->status);
        }

        if ($request->filled('is_approved')) {
            $query->where('is_approved', $request->boolean('is_approved'));
        }

        $curricula = $query->latest()->paginate(15);
        $subjects = Subject::orderBy('name')->get();
        $levels = Level::orderBy('rank')->get();
        $levelGroups = LevelGroup::orderBy('display_order')->get();

        return view('admin.curriculum.index', compact('curricula', 'subjects', 'levels', 'levelGroups'));
    }

    /**
     * Store a newly uploaded curriculum PDF.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'education_body' => 'required|string|max:100',
            'academic_year' => 'nullable|string|max:50',
            'subject_id' => 'required|exists:subjects,id',
            'level_id' => 'nullable|exists:levels,id',
            'level_group_id' => 'nullable|exists:level_groups,id',
            'curriculum_file' => 'required|file|mimes:pdf|max:51200', // max 50MB
            'notes' => 'nullable|string|max:2000',
        ]);

        $file = $request->file('curriculum_file');
        $fileName = 'curriculum_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('curricula', $fileName, 'public');

        $curriculum = Curriculum::create([
            'title' => $request->title,
            'education_body' => $request->education_body,
            'academic_year' => $request->academic_year,
            'subject_id' => $request->subject_id,
            'level_id' => $request->level_id,
            'level_group_id' => $request->level_group_id,
            'file_path' => $filePath,
            'file_size_bytes' => $file->getSize(),
            'extraction_status' => 'pending',
            'uploaded_by' => Auth::id(),
            'notes' => $request->notes,
        ]);

        // Dispatch background extraction job
        ProcessCurriculumExtractionJob::dispatch($curriculum->id);

        return redirect()->route('admin.curriculum.show', $curriculum)
            ->with('success', 'Curriculum uploaded! Background AI extraction has started.');
    }

    /**
     * Display the specified curriculum for review/editing.
     */
    public function show(Curriculum $curriculum)
    {
        $curriculum->load([
            'subject',
            'level',
            'levelGroup',
            'uploader',
            'approver',
            'strands.media',
            'strands.subStrands.media',
            'strands.subStrands.indicators.media',
            'strands.subStrands.indicators.textbookSections',
        ]);

        $subjects = Subject::orderBy('name')->get();
        $levels = Level::orderBy('rank')->get();

        return view('admin.curriculum.show', compact('curriculum', 'subjects', 'levels'));
    }

    /**
     * Approve curriculum to make it the active learning engine.
     */
    public function approve(Curriculum $curriculum)
    {
        $curriculum->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Curriculum approved! It is now active as the guided learning backbone.');
    }

    /**
     * Trigger re-extraction of the curriculum.
     */
    public function reExtract(Curriculum $curriculum)
    {
        $curriculum->update([
            'extraction_status' => 'pending',
            'extraction_error' => null,
        ]);

        ProcessCurriculumExtractionJob::dispatch($curriculum->id);

        return back()->with('success', 'Re-extraction job queued successfully.');
    }

    /**
     * Check extraction status via AJAX.
     */
    public function status(Curriculum $curriculum): JsonResponse
    {
        return response()->json([
            'status' => $curriculum->extraction_status,
            'error' => $curriculum->extraction_error,
            'is_approved' => $curriculum->is_approved,
            'strands_count' => $curriculum->strands()->count(),
            'total_indicators' => $curriculum->totalIndicatorsCount(),
        ]);
    }

    /**
     * AJAX update for a curriculum strand.
     */
    public function updateStrand(Request $request, CurriculumStrand $strand): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grade_label' => 'nullable|string|max:50',
            'level_id' => 'nullable|exists:levels,id',
        ]);

        $strand->update($validated);

        return response()->json(['success' => true, 'strand' => $strand]);
    }

    /**
     * AJAX update for a sub-strand.
     */
    public function updateSubStrand(Request $request, CurriculumSubStrand $subStrand): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_standard' => 'nullable|string',
        ]);

        $subStrand->update($validated);

        return response()->json(['success' => true, 'subStrand' => $subStrand]);
    }

    /**
     * AJAX update for an indicator.
     */
    public function updateIndicator(Request $request, CurriculumIndicator $indicator): JsonResponse
    {
        $validated = $request->validate([
            'indicator_code' => 'nullable|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'exemplars' => 'nullable|string',
        ]);

        $indicator->update($validated);

        return response()->json(['success' => true, 'indicator' => $indicator]);
    }

    /**
     * Delete a curriculum and associated file.
     */
    public function destroy(Curriculum $curriculum)
    {
        if ($curriculum->file_path && Storage::disk('public')->exists($curriculum->file_path)) {
            Storage::disk('public')->delete($curriculum->file_path);
        }

        $curriculum->delete();

        return redirect()->route('admin.curriculum.index')
            ->with('success', 'Curriculum deleted successfully.');
    }
}
