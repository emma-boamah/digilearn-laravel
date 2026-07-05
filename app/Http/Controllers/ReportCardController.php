<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolClass;
use App\Models\AcademicTerm;
use App\Models\ReportCard;
use App\Services\GradebookService;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportCardController extends Controller
{
    /**
     * Display report cards index.
     */
    public function index()
    {
        $teacher = Auth::user();

        // Assuming school-admin can see all classes, teacher sees their own
        $classesQuery = SchoolClass::where('school_id', $teacher->school_id);
        
        if ($teacher->hasRole('teacher') && !$teacher->hasRole('school-admin')) {
            $classesQuery->whereHas('students', function ($q) use ($teacher) {
                // Simplified for now - can be expanded to check specific subject assignments
            });
        }
        
        $classes = $classesQuery->get();
        $terms = AcademicTerm::whereHas('academicYear', function ($q) use ($teacher) {
            $q->where('school_id', $teacher->school_id);
        })->get();

        return view('dashboard.teacher.reports.index', compact('classes', 'terms'));
    }

    /**
     * Generate report cards for a class.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
        ]);

        // Ensure authorization
        $teacher = Auth::user();
        $class = SchoolClass::findOrFail($request->school_class_id);
        if ($class->school_id !== $teacher->school_id) {
            abort(403);
        }

        $service = new GradebookService();
        $service->generateClassReportCards($request->school_class_id, $request->academic_term_id);

        return back()->with('success', 'Report cards generated successfully!');
    }

    /**
     * View generated report cards for a class.
     */
    public function viewClassReports($classId, $termId)
    {
        $teacher = Auth::user();
        $schoolClass = SchoolClass::where('school_id', $teacher->school_id)->findOrFail($classId);
        $term = AcademicTerm::findOrFail($termId);

        $reportCards = ReportCard::with(['student', 'details.subject'])
            ->where('school_class_id', $classId)
            ->where('academic_term_id', $termId)
            ->orderBy('position_in_class', 'asc')
            ->get();

        return view('dashboard.teacher.reports.view', compact('schoolClass', 'term', 'reportCards'));
    }

    /**
     * Download a specific report card as PDF.
     */
    public function downloadPdf($id)
    {
        $teacher = Auth::user();
        $reportCard = ReportCard::with(['student', 'details.subject', 'schoolClass.school', 'schoolClass.students', 'academicTerm.academicYear'])
            ->findOrFail($id);

        // Security check - ensure they belong to the same school
        if ($reportCard->schoolClass->school_id !== $teacher->school_id) {
            abort(403);
        }

        $data = [
            'reportCard' => $reportCard,
            'school' => $reportCard->schoolClass->school,
            'student' => $reportCard->student,
            'term' => $reportCard->academicTerm,
        ];

        $pdf = Pdf::loadView('pdf.report-card', $data)->setPaper('a4', 'portrait');
        $filename = 'Report_Card_' . str_replace(' ', '_', $reportCard->student->name) . '_' . $reportCard->academicTerm->term_name . '.pdf';

        return $pdf->download($filename);
    }
}
