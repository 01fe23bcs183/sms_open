<?php

namespace App\Services;

use App\Models\ExamMark;
use App\Models\ExamSchedule;
use App\Models\ExamGrade;
use App\Models\Student;
use App\Models\Exam;
use Illuminate\Support\Facades\DB;

/**
 * Result Service
 * 
 * Prompt 328: Create Result Service
 * 
 * Computes student results and rankings. Aggregates marks and applies
 * grading scales. Handles pass/fail logic and generates report card data.
 */
class ResultService
{
    /**
     * Enter marks for a student.
     * 
     * @param int $examScheduleId
     * @param int $studentId
     * @param float $obtainedMarks
     * @param string|null $remarks
     * @return ExamMark
     */
    public function enterMarks(
        int $examScheduleId,
        int $studentId,
        float $obtainedMarks,
        ?string $remarks = null
    ): ExamMark {
        $schedule = ExamSchedule::findOrFail($examScheduleId);
        
        // Calculate grade
        $percentage = ($obtainedMarks / $schedule->full_marks) * 100;
        $grade = $this->getGradeForPercentage($percentage);
        
        return ExamMark::updateOrCreate(
            [
                'exam_schedule_id' => $examScheduleId,
                'student_id' => $studentId,
            ],
            [
                'obtained_marks' => $obtainedMarks,
                'grade_id' => $grade?->id,
                'remarks' => $remarks,
            ]
        );
    }

    /**
     * Enter bulk marks for a schedule.
     * 
     * @param int $examScheduleId
     * @param array $marksData Array of ['student_id' => ['marks' => float, 'remarks' => string|null]]
     * @return int Number of records created/updated
     */
    public function enterBulkMarks(int $examScheduleId, array $marksData): int
    {
        $count = 0;
        
        DB::transaction(function () use ($examScheduleId, $marksData, &$count) {
            foreach ($marksData as $studentId => $data) {
                $this->enterMarks(
                    $examScheduleId,
                    $studentId,
                    $data['marks'],
                    $data['remarks'] ?? null
                );
                $count++;
            }
        });
        
        return $count;
    }

    /**
     * Get student result for an exam.
     * 
     * @param int $examId
     * @param int $studentId
     * @return array
     */
    public function getStudentResult(int $examId, int $studentId): array
    {
        $student = Student::with('user')->findOrFail($studentId);
        $exam = Exam::with('examType')->findOrFail($examId);
        
        $schedules = ExamSchedule::with(['subject'])
            ->where('exam_id', $examId)
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->get();
        
        $results = [];
        $totalObtained = 0;
        $totalFull = 0;
        $totalPassing = 0;
        $passedSubjects = 0;
        $failedSubjects = 0;
        
        foreach ($schedules as $schedule) {
            $mark = ExamMark::with('grade')
                ->where('exam_schedule_id', $schedule->id)
                ->where('student_id', $studentId)
                ->first();
            
            $obtained = $mark?->obtained_marks ?? 0;
            $isPassed = $obtained >= $schedule->passing_marks;
            
            $results[] = [
                'subject_id' => $schedule->subject_id,
                'subject_name' => $schedule->subject->name ?? '',
                'full_marks' => $schedule->full_marks,
                'passing_marks' => $schedule->passing_marks,
                'obtained_marks' => $obtained,
                'percentage' => $schedule->full_marks > 0 ? round(($obtained / $schedule->full_marks) * 100, 2) : 0,
                'grade' => $mark?->grade?->name ?? '',
                'grade_point' => $mark?->grade?->grade_point ?? 0,
                'is_passed' => $isPassed,
                'remarks' => $mark?->remarks ?? '',
            ];
            
            $totalObtained += $obtained;
            $totalFull += $schedule->full_marks;
            $totalPassing += $schedule->passing_marks;
            
            if ($isPassed) {
                $passedSubjects++;
            } else {
                $failedSubjects++;
            }
        }
        
        $overallPercentage = $totalFull > 0 ? round(($totalObtained / $totalFull) * 100, 2) : 0;
        $overallGrade = $this->getGradeForPercentage($overallPercentage);
        $overallPassed = $failedSubjects === 0;
        
        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->user->full_name ?? '',
                'admission_number' => $student->admission_number,
                'roll_number' => $student->roll_number,
                'class' => $student->schoolClass->name ?? '',
                'section' => $student->section->name ?? '',
            ],
            'exam' => [
                'id' => $exam->id,
                'name' => $exam->name,
                'type' => $exam->examType->name ?? '',
            ],
            'subjects' => $results,
            'summary' => [
                'total_subjects' => count($results),
                'passed_subjects' => $passedSubjects,
                'failed_subjects' => $failedSubjects,
                'total_obtained' => $totalObtained,
                'total_full' => $totalFull,
                'percentage' => $overallPercentage,
                'grade' => $overallGrade?->name ?? '',
                'grade_point' => $overallGrade?->grade_point ?? 0,
                'result' => $overallPassed ? 'Pass' : 'Fail',
            ],
        ];
    }

    /**
     * Get class results for an exam.
     * 
     * @param int $examId
     * @param int $classId
     * @param int $sectionId
     * @return array
     */
    public function getClassResults(int $examId, int $classId, int $sectionId): array
    {
        $students = Student::with('user')
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('is_active', true)
            ->orderBy('roll_number')
            ->get();
        
        $results = [];
        foreach ($students as $student) {
            $results[] = $this->getStudentResult($examId, $student->id);
        }
        
        // Sort by percentage for ranking
        usort($results, function ($a, $b) {
            return $b['summary']['percentage'] <=> $a['summary']['percentage'];
        });
        
        // Add ranks
        $rank = 1;
        foreach ($results as &$result) {
            $result['rank'] = $rank++;
        }
        
        return $results;
    }

    /**
     * Generate report card data for a student.
     * 
     * @param int $studentId
     * @param int|null $sessionId
     * @return array
     */
    public function generateReportCard(int $studentId, ?int $sessionId = null): array
    {
        $student = Student::with(['user', 'schoolClass', 'section', 'academicSession'])
            ->findOrFail($studentId);
        
        $sessionId = $sessionId ?? $student->academic_session_id;
        
        $exams = Exam::with('examType')
            ->where('academic_session_id', $sessionId)
            ->where('is_active', true)
            ->orderBy('start_date')
            ->get();
        
        $examResults = [];
        foreach ($exams as $exam) {
            $examResults[] = $this->getStudentResult($exam->id, $studentId);
        }
        
        // Calculate overall performance
        $totalPercentage = 0;
        $examCount = count($examResults);
        
        foreach ($examResults as $result) {
            $totalPercentage += $result['summary']['percentage'];
        }
        
        $averagePercentage = $examCount > 0 ? round($totalPercentage / $examCount, 2) : 0;
        $overallGrade = $this->getGradeForPercentage($averagePercentage);
        
        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->user->full_name ?? '',
                'admission_number' => $student->admission_number,
                'roll_number' => $student->roll_number,
                'class' => $student->schoolClass->name ?? '',
                'section' => $student->section->name ?? '',
                'session' => $student->academicSession->name ?? '',
                'date_of_birth' => $student->date_of_birth?->format('Y-m-d'),
                'father_name' => $student->father_name,
                'mother_name' => $student->mother_name,
            ],
            'exams' => $examResults,
            'overall' => [
                'average_percentage' => $averagePercentage,
                'grade' => $overallGrade?->name ?? '',
                'grade_point' => $overallGrade?->grade_point ?? 0,
            ],
        ];
    }

    /**
     * Get class ranking for an exam.
     * 
     * @param int $examId
     * @param int $classId
     * @param int $sectionId
     * @return array
     */
    public function getClassRanking(int $examId, int $classId, int $sectionId): array
    {
        $results = $this->getClassResults($examId, $classId, $sectionId);
        
        return array_map(function ($result) {
            return [
                'rank' => $result['rank'],
                'student_id' => $result['student']['id'],
                'student_name' => $result['student']['name'],
                'admission_number' => $result['student']['admission_number'],
                'roll_number' => $result['student']['roll_number'],
                'total_obtained' => $result['summary']['total_obtained'],
                'total_full' => $result['summary']['total_full'],
                'percentage' => $result['summary']['percentage'],
                'grade' => $result['summary']['grade'],
                'result' => $result['summary']['result'],
            ];
        }, $results);
    }

    /**
     * Get subject-wise analysis for a class.
     * 
     * @param int $examId
     * @param int $classId
     * @param int $sectionId
     * @return array
     */
    public function getSubjectAnalysis(int $examId, int $classId, int $sectionId): array
    {
        $schedules = ExamSchedule::with('subject')
            ->where('exam_id', $examId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->get();
        
        $analysis = [];
        
        foreach ($schedules as $schedule) {
            $marks = ExamMark::where('exam_schedule_id', $schedule->id)->get();
            
            $totalStudents = $marks->count();
            $passed = $marks->filter(fn($m) => $m->obtained_marks >= $schedule->passing_marks)->count();
            $failed = $totalStudents - $passed;
            $highest = $marks->max('obtained_marks') ?? 0;
            $lowest = $marks->min('obtained_marks') ?? 0;
            $average = $totalStudents > 0 ? round($marks->avg('obtained_marks'), 2) : 0;
            
            $analysis[] = [
                'subject_id' => $schedule->subject_id,
                'subject_name' => $schedule->subject->name ?? '',
                'full_marks' => $schedule->full_marks,
                'passing_marks' => $schedule->passing_marks,
                'total_students' => $totalStudents,
                'passed' => $passed,
                'failed' => $failed,
                'pass_percentage' => $totalStudents > 0 ? round(($passed / $totalStudents) * 100, 2) : 0,
                'highest_marks' => $highest,
                'lowest_marks' => $lowest,
                'average_marks' => $average,
                'average_percentage' => $schedule->full_marks > 0 ? round(($average / $schedule->full_marks) * 100, 2) : 0,
            ];
        }
        
        return $analysis;
    }

    /**
     * Get grade for a percentage.
     * 
     * @param float $percentage
     * @return ExamGrade|null
     */
    private function getGradeForPercentage(float $percentage): ?ExamGrade
    {
        return ExamGrade::where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->first();
    }

    /**
     * Get result statistics for an exam.
     * 
     * @param int $examId
     * @return array
     */
    public function getExamStatistics(int $examId): array
    {
        $schedules = ExamSchedule::where('exam_id', $examId)->get();
        
        $totalMarksEntered = 0;
        $totalStudents = 0;
        
        foreach ($schedules as $schedule) {
            $marksCount = ExamMark::where('exam_schedule_id', $schedule->id)->count();
            $studentsCount = Student::where('class_id', $schedule->class_id)
                ->where('section_id', $schedule->section_id)
                ->where('is_active', true)
                ->count();
            
            $totalMarksEntered += $marksCount;
            $totalStudents += $studentsCount;
        }
        
        return [
            'total_schedules' => $schedules->count(),
            'total_students' => $totalStudents,
            'marks_entered' => $totalMarksEntered,
            'marks_pending' => $totalStudents - $totalMarksEntered,
            'completion_percentage' => $totalStudents > 0 ? round(($totalMarksEntered / $totalStudents) * 100, 2) : 0,
        ];
    }
}
