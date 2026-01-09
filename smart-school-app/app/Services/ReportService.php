<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Attendance;
use App\Models\FeesTransaction;
use App\Models\ExamMark;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Report Service
 * 
 * Prompt 304: Add Report Export Endpoints with Filters
 * 
 * Handles data retrieval and filtering for various reports.
 */
class ReportService
{
    /**
     * Get students report data with filters.
     * 
     * @param array $filters
     * @return Collection
     */
    public function getStudentsReport(array $filters = []): Collection
    {
        $query = Student::query()
            ->with(['schoolClass', 'section', 'academicSession'])
            ->select([
                'students.id',
                'students.admission_number',
                'students.first_name',
                'students.last_name',
                'students.email',
                'students.phone',
                'students.gender',
                'students.date_of_birth',
                'students.class_id',
                'students.section_id',
                'students.academic_session_id',
                'students.status',
                'students.created_at',
            ]);

        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (!empty($filters['section_id'])) {
            $query->where('section_id', $filters['section_id']);
        }

        if (!empty($filters['academic_session_id'])) {
            $query->where('academic_session_id', $filters['academic_session_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('first_name')->get()->map(function ($student) {
            return [
                'ID' => $student->id,
                'Admission No' => $student->admission_number,
                'First Name' => $student->first_name,
                'Last Name' => $student->last_name,
                'Email' => $student->email,
                'Phone' => $student->phone,
                'Gender' => ucfirst($student->gender ?? ''),
                'Date of Birth' => $student->date_of_birth?->format('Y-m-d'),
                'Class' => $student->schoolClass?->name ?? '',
                'Section' => $student->section?->name ?? '',
                'Session' => $student->academicSession?->name ?? '',
                'Status' => ucfirst($student->status ?? ''),
                'Enrolled Date' => $student->created_at?->format('Y-m-d'),
            ];
        });
    }

    /**
     * Get attendance report data with filters.
     * 
     * @param array $filters
     * @return Collection
     */
    public function getAttendanceReport(array $filters = []): Collection
    {
        $query = Attendance::query()
            ->with(['student', 'schoolClass', 'section'])
            ->select([
                'attendances.id',
                'attendances.student_id',
                'attendances.class_id',
                'attendances.section_id',
                'attendances.date',
                'attendances.status',
                'attendances.remarks',
                'attendances.created_at',
            ]);

        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (!empty($filters['section_id'])) {
            $query->where('section_id', $filters['section_id']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        return $query->orderByDesc('date')->get()->map(function ($attendance) {
            return [
                'ID' => $attendance->id,
                'Date' => $attendance->date?->format('Y-m-d'),
                'Student Name' => $attendance->student ? 
                    "{$attendance->student->first_name} {$attendance->student->last_name}" : '',
                'Admission No' => $attendance->student?->admission_number ?? '',
                'Class' => $attendance->schoolClass?->name ?? '',
                'Section' => $attendance->section?->name ?? '',
                'Status' => ucfirst($attendance->status ?? ''),
                'Remarks' => $attendance->remarks ?? '',
            ];
        });
    }

    /**
     * Get fees report data with filters.
     * 
     * @param array $filters
     * @return Collection
     */
    public function getFeesReport(array $filters = []): Collection
    {
        $query = FeesTransaction::query()
            ->with(['student', 'feesType'])
            ->select([
                'fees_transactions.id',
                'fees_transactions.student_id',
                'fees_transactions.fees_type_id',
                'fees_transactions.amount',
                'fees_transactions.payment_method',
                'fees_transactions.payment_status',
                'fees_transactions.transaction_id',
                'fees_transactions.payment_date',
                'fees_transactions.remarks',
                'fees_transactions.created_at',
            ]);

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['fees_type_id'])) {
            $query->where('fees_type_id', $filters['fees_type_id']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('payment_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('payment_date', '<=', $filters['date_to']);
        }

        return $query->orderByDesc('payment_date')->get()->map(function ($transaction) {
            return [
                'ID' => $transaction->id,
                'Transaction ID' => $transaction->transaction_id ?? '',
                'Student Name' => $transaction->student ? 
                    "{$transaction->student->first_name} {$transaction->student->last_name}" : '',
                'Admission No' => $transaction->student?->admission_number ?? '',
                'Fee Type' => $transaction->feesType?->name ?? '',
                'Amount' => number_format($transaction->amount, 2),
                'Payment Method' => ucfirst($transaction->payment_method ?? ''),
                'Payment Status' => ucfirst($transaction->payment_status ?? ''),
                'Payment Date' => $transaction->payment_date?->format('Y-m-d'),
                'Remarks' => $transaction->remarks ?? '',
            ];
        });
    }

    /**
     * Get exams report data with filters.
     * 
     * @param array $filters
     * @return Collection
     */
    public function getExamsReport(array $filters = []): Collection
    {
        $query = ExamMark::query()
            ->with(['student', 'examSchedule.exam', 'examSchedule.subject'])
            ->select([
                'exam_marks.id',
                'exam_marks.student_id',
                'exam_marks.exam_schedule_id',
                'exam_marks.obtained_marks',
                'exam_marks.remarks',
                'exam_marks.created_at',
            ]);

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['exam_id'])) {
            $query->whereHas('examSchedule', function ($q) use ($filters) {
                $q->where('exam_id', $filters['exam_id']);
            });
        }

        if (!empty($filters['class_id'])) {
            $query->whereHas('examSchedule', function ($q) use ($filters) {
                $q->where('class_id', $filters['class_id']);
            });
        }

        if (!empty($filters['subject_id'])) {
            $query->whereHas('examSchedule', function ($q) use ($filters) {
                $q->where('subject_id', $filters['subject_id']);
            });
        }

        return $query->orderByDesc('created_at')->get()->map(function ($mark) {
            $schedule = $mark->examSchedule;
            
            return [
                'ID' => $mark->id,
                'Student Name' => $mark->student ? 
                    "{$mark->student->first_name} {$mark->student->last_name}" : '',
                'Admission No' => $mark->student?->admission_number ?? '',
                'Exam' => $schedule?->exam?->name ?? '',
                'Subject' => $schedule?->subject?->name ?? '',
                'Full Marks' => $schedule?->full_marks ?? '',
                'Passing Marks' => $schedule?->passing_marks ?? '',
                'Obtained Marks' => $mark->obtained_marks,
                'Percentage' => $schedule?->full_marks > 0 
                    ? round(($mark->obtained_marks / $schedule->full_marks) * 100, 2) . '%' 
                    : '',
                'Result' => $schedule?->passing_marks && $mark->obtained_marks >= $schedule->passing_marks 
                    ? 'Pass' 
                    : 'Fail',
                'Remarks' => $mark->remarks ?? '',
            ];
        });
    }

    /**
     * Get report data by module.
     * 
     * @param string $module
     * @param array $filters
     * @return Collection
     */
    public function getReportData(string $module, array $filters = []): Collection
    {
        return match ($module) {
            'students' => $this->getStudentsReport($filters),
            'attendance' => $this->getAttendanceReport($filters),
            'fees' => $this->getFeesReport($filters),
            'exams' => $this->getExamsReport($filters),
            default => collect([]),
        };
    }

    /**
     * Get available modules for export.
     * 
     * @return array
     */
    public function getAvailableModules(): array
    {
        return [
            'students' => 'Students Report',
            'attendance' => 'Attendance Report',
            'fees' => 'Fees Report',
            'exams' => 'Exams Report',
        ];
    }
}
