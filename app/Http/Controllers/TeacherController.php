<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSubject;
use App\Models\AcademicTerm;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Services\GradebookService;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    /**
     * View assigned classes for the logged-in teacher.
     */
    public function classes(Request $request)
    {
        $teacher = Auth::user();

        if (!$teacher->school_id) {
            return redirect()->route('dashboard.main')->with('error', 'You are not assigned to any school.');
        }

        // Get classes the teacher is assigned to
        $classSubjects = ClassSubject::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->get();

        return view('dashboard.teacher.classes', compact('classSubjects'));
    }

    /**
     * View the gradebook for a specific class and subject.
     */
    public function gradebook(Request $request, $classSubjectId, $termId = null)
    {
        $teacher = Auth::user();
        
        $classSubject = ClassSubject::with(['schoolClass.students', 'subject'])->findOrFail($classSubjectId);
        
        // Basic authorization - ensure the teacher is assigned to this class or is an admin
        if ($classSubject->teacher_id !== $teacher->id && !$teacher->hasRole('school-admin')) {
            abort(403, 'Unauthorized access to this gradebook.');
        }

        // Get the active term if no term ID is provided
        if (!$termId) {
            $term = AcademicTerm::whereHas('academicYear', function ($q) use ($teacher) {
                $q->where('school_id', $teacher->school_id)->where('is_active', true);
            })->latest()->first();
            
            if (!$term) {
                return redirect()->route('teacher.classes')->with('error', 'No active academic term found. Please contact admin.');
            }
            $termId = $term->id;
        } else {
            $term = AcademicTerm::findOrFail($termId);
        }

        // Get all assessments for this term and class
        $assessments = Assessment::where('class_subject_id', $classSubjectId)
            ->where('academic_term_id', $termId)
            ->orderBy('date_administered', 'asc')
            ->get();

        // Get all scores structured for the view
        $scoresRaw = AssessmentScore::whereIn('assessment_id', $assessments->pluck('id'))->get();
        $scores = [];
        foreach ($scoresRaw as $score) {
            $scores[$score->student_id][$score->assessment_id] = $score;
        }

        $students = $classSubject->schoolClass->students;
        
        // Calculate final grades on the fly for display
        $gradebookService = new GradebookService();
        $finalGrades = [];
        foreach ($students as $student) {
            $finalGrades[$student->id] = $gradebookService->calculateSubjectGrade($student->id, $classSubjectId, $termId);
        }

        $allTerms = AcademicTerm::whereHas('academicYear', function ($q) use ($teacher) {
            $q->where('school_id', $teacher->school_id);
        })->get();

        return view('dashboard.teacher.gradebook', compact('classSubject', 'term', 'allTerms', 'assessments', 'students', 'scores', 'finalGrades'));
    }

    /**
     * Store a new assessment column.
     */
    public function storeAssessment(Request $request)
    {
        $validated = $request->validate([
            'class_subject_id' => 'required|exists:class_subjects,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:exercise,homework,project,exam,mid_term',
            'max_score' => 'required|numeric|min:1',
            'weight_percentage' => 'required|numeric|min:0|max:100',
            'date_administered' => 'nullable|date',
        ]);

        $teacher = Auth::user();
        $validated['school_id'] = $teacher->school_id;

        Assessment::create($validated);

        return back()->with('success', 'Assessment created successfully.');
    }

    /**
     * Save scores in bulk from the gradebook.
     */
    public function saveScores(Request $request)
    {
        $validated = $request->validate([
            'scores' => 'required|array',
            'scores.*.*' => 'nullable|numeric|min:0', // scores[assessment_id][student_id]
        ]);

        foreach ($validated['scores'] as $assessmentId => $studentScores) {
            foreach ($studentScores as $studentId => $scoreValue) {
                if ($scoreValue === null || $scoreValue === '') {
                    continue; // Skip empty inputs
                }

                AssessmentScore::updateOrCreate(
                    [
                        'assessment_id' => $assessmentId,
                        'student_id' => $studentId,
                    ],
                    [
                        'score' => $scoreValue,
                    ]
                );
            }
        }

        return back()->with('success', 'Scores saved successfully.');
    }

    /**
     * Get available quizzes for CBT assignment (AJAX endpoint).
     * Filters published quizzes by the class subject's grade level and subject.
     */
    public function availableQuizzes(Request $request)
    {
        $request->validate([
            'class_subject_id' => 'required|exists:class_subjects,id',
        ]);

        $classSubject = ClassSubject::with(['schoolClass.level', 'subject'])->findOrFail($request->class_subject_id);

        // Match quizzes by subject name and grade level of the class
        $gradeLevelTitle = $classSubject->schoolClass->level->title ?? null;

        $quizzes = \App\Models\Quiz::published()
            ->when($classSubject->subject, function ($q) use ($classSubject) {
                $q->whereHas('subject', function ($sq) use ($classSubject) {
                    $sq->where('name', $classSubject->subject->name);
                });
            })
            ->when($gradeLevelTitle, function ($q) use ($gradeLevelTitle) {
                $q->where('grade_level', $gradeLevelTitle);
            })
            ->select('id', 'title', 'grade_level', 'time_limit_minutes')
            ->orderBy('title')
            ->get();

        return response()->json($quizzes);
    }

    /**
     * Assign a quiz as a CBT assessment for a class subject.
     */
    public function assignCbt(Request $request)
    {
        $validated = $request->validate([
            'class_subject_id' => 'required|exists:class_subjects,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'quiz_id' => 'required|exists:quizzes,id',
            'max_score' => 'required|numeric|min:1',
            'weight_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $teacher = Auth::user();
        $classSubject = ClassSubject::findOrFail($validated['class_subject_id']);

        // Authorization: teacher must be assigned to this class or be a school admin
        if ($classSubject->teacher_id !== $teacher->id && !$teacher->hasRole('school-admin')) {
            abort(403, 'Unauthorized.');
        }

        $quiz = \App\Models\Quiz::findOrFail($validated['quiz_id']);

        // Check if this quiz is already linked as a CBT for this class/term
        $existing = Assessment::where('quiz_id', $quiz->id)
            ->where('class_subject_id', $classSubject->id)
            ->where('academic_term_id', $validated['academic_term_id'])
            ->first();

        if ($existing) {
            return back()->with('error', 'This quiz is already assigned as a CBT for this class and term.');
        }

        Assessment::create([
            'school_id' => $teacher->school_id,
            'class_subject_id' => $classSubject->id,
            'academic_term_id' => $validated['academic_term_id'],
            'quiz_id' => $quiz->id,
            'title' => 'CBT: ' . $quiz->title,
            'type' => 'cbt',
            'max_score' => $validated['max_score'],
            'weight_percentage' => $validated['weight_percentage'],
            'date_administered' => now(),
        ]);

        return back()->with('success', 'Quiz "' . $quiz->title . '" assigned as a CBT assessment successfully.');
    }
}
