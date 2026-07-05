<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\ClassSubject;
use App\Models\User;
use Illuminate\Support\Collection;

class GradebookService
{
    /**
     * Calculate the final terminal grade for a student in a specific class subject.
     *
     * @param int $studentId
     * @param int $classSubjectId
     * @param int $academicTermId
     * @return array
     */
    public function calculateSubjectGrade(int $studentId, int $classSubjectId, int $academicTermId): array
    {
        // Get all assessments for the given term and class subject
        $assessments = Assessment::where('class_subject_id', $classSubjectId)
            ->where('academic_term_id', $academicTermId)
            ->get();

        if ($assessments->isEmpty()) {
            return [
                'total_score' => 0,
                'class_score' => 0,
                'exam_score' => 0,
                'grade' => 'N/A',
                'remarks' => 'No assessments found.'
            ];
        }

        $classScoreSum = 0;
        $classScoreMax = 0;
        $examScoreSum = 0;
        $examScoreMax = 0;

        foreach ($assessments as $assessment) {
            $scoreRecord = AssessmentScore::where('assessment_id', $assessment->id)
                ->where('student_id', $studentId)
                ->first();

            $score = $scoreRecord ? $scoreRecord->score : 0;
            
            // Normalize score based on weight. e.g. score of 50/100 with weight 30% = 15
            $weightedScore = ($score / $assessment->max_score) * $assessment->weight_percentage;

            if ($assessment->type === 'exam') {
                $examScoreSum += $weightedScore;
                $examScoreMax += $assessment->weight_percentage;
            } else {
                // All other types (exercise, homework, project, mid_term) are considered continuous assessment (class score)
                $classScoreSum += $weightedScore;
                $classScoreMax += $assessment->weight_percentage;
            }
        }

        $totalScore = $classScoreSum + $examScoreSum;

        return [
            'total_score' => round($totalScore, 2),
            'class_score' => round($classScoreSum, 2),
            'exam_score' => round($examScoreSum, 2),
            'grade' => $this->determineGrade($totalScore),
            'remarks' => $this->determineRemarks($totalScore),
        ];
    }

    /**
     * Determine letter grade based on Ghanaian Standard (WAEC/GES format approximation).
     * This can later be made dynamic based on SchoolSettings.
     *
     * @param float $score
     * @return string
     */
    public function determineGrade(float $score): string
    {
        if ($score >= 80) return 'A'; // 1 (Excellent)
        if ($score >= 70) return 'B'; // 2 (Very Good)
        if ($score >= 60) return 'C'; // 3 (Good)
        if ($score >= 50) return 'D'; // 4 (Credit)
        if ($score >= 40) return 'E'; // 5 (Pass)
        return 'F'; // 9 (Fail)
    }

    /**
     * Determine remarks based on the final score.
     *
     * @param float $score
     * @return string
     */
    public function determineRemarks(float $score): string
    {
        if ($score >= 80) return 'Excellent performance. Keep it up!';
        if ($score >= 70) return 'Very good work.';
        if ($score >= 60) return 'Good effort.';
        if ($score >= 50) return 'Satisfactory. You can do better.';
        if ($score >= 40) return 'Passed, but needs serious improvement.';
        return 'Failed. Needs extra tuition and focus.';
    }

    /**
     * Generate report cards for an entire class for a specific term.
     *
     * @param int $schoolClassId
     * @param int $academicTermId
     * @return void
     */
    public function generateClassReportCards(int $schoolClassId, int $academicTermId): void
    {
        $schoolClass = \App\Models\SchoolClass::with('students')->findOrFail($schoolClassId);
        $classSubjects = ClassSubject::where('school_class_id', $schoolClassId)->get();

        foreach ($schoolClass->students as $student) {
            $totalClassScore = 0;
            $subjectCount = 0;

            $reportCard = \App\Models\ReportCard::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_term_id' => $academicTermId,
                    'school_class_id' => $schoolClassId,
                ],
                [
                    'teacher_remarks' => 'Generated automatically.',
                ]
            );

            foreach ($classSubjects as $classSubject) {
                $subjectGrade = $this->calculateSubjectGrade($student->id, $classSubject->id, $academicTermId);
                
                \App\Models\ReportCardDetail::updateOrCreate(
                    [
                        'report_card_id' => $reportCard->id,
                        'subject_id' => $classSubject->subject_id,
                    ],
                    [
                        'class_score' => $subjectGrade['class_score'],
                        'exam_score' => $subjectGrade['exam_score'],
                        'total_score' => $subjectGrade['total_score'],
                        'grade' => $subjectGrade['grade'],
                        'remarks' => $subjectGrade['remarks'],
                    ]
                );

                $totalClassScore += $subjectGrade['total_score'];
                $subjectCount++;
            }

            if ($subjectCount > 0) {
                $reportCard->update([
                    'total_score' => $totalClassScore,
                    'average_score' => $totalClassScore / $subjectCount,
                ]);
            }
        }

        // Calculate positions
        $reportCards = \App\Models\ReportCard::where('school_class_id', $schoolClassId)
            ->where('academic_term_id', $academicTermId)
            ->orderByDesc('total_score')
            ->get();
            
        $position = 1;
        foreach ($reportCards as $rc) {
            $rc->update(['position_in_class' => $position]);
            $position++;
        }
    }

    /**
     * Record a CBT quiz result as an AssessmentScore in the gradebook.
     *
     * Called after a school student completes a quiz that is linked to a CBT assessment.
     * Individual users (school_id = null) never reach this method.
     *
     * @param int $studentId
     * @param int $quizId
     * @param float $scorePercentage  The percentage score from the QuizAttempt (0-100)
     * @return bool  True if a score was recorded, false if no matching assessment was found.
     */
    public function recordCbtScore(int $studentId, int $quizId, float $scorePercentage): bool
    {
        $student = User::find($studentId);

        if (!$student || !$student->school_id || !$student->school_class_id) {
            return false;
        }

        // Find CBT assessments that link to this quiz AND belong to the student's class
        $assessments = Assessment::where('quiz_id', $quizId)
            ->where('type', 'cbt')
            ->whereHas('classSubject', function ($q) use ($student) {
                $q->where('school_class_id', $student->school_class_id);
            })
            ->get();

        if ($assessments->isEmpty()) {
            return false;
        }

        foreach ($assessments as $assessment) {
            // Normalize percentage to this assessment's max_score
            // e.g., 85% on a quiz with max_score of 50 => 42.5
            $normalizedScore = ($scorePercentage / 100) * $assessment->max_score;

            AssessmentScore::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'student_id' => $studentId,
                ],
                [
                    'score' => round($normalizedScore, 2),
                ]
            );
        }

        return true;
    }
}
