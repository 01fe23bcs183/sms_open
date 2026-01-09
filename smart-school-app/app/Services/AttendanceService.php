<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceType;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Attendance Service
 * 
 * Prompt 326: Create Attendance Service
 * 
 * Centralizes attendance rules and calculations. Handles daily and bulk
 * attendance marking, prevents duplicates, and generates summaries.
 */
class AttendanceService
{
    /**
     * Mark attendance for a single student.
     * 
     * @param int $studentId
     * @param int $classId
     * @param int $sectionId
     * @param string $date
     * @param int $attendanceTypeId
     * @param int|null $markedBy
     * @param string|null $remarks
     * @return Attendance
     */
    public function markAttendance(
        int $studentId,
        int $classId,
        int $sectionId,
        string $date,
        int $attendanceTypeId,
        ?int $markedBy = null,
        ?string $remarks = null
    ): Attendance {
        return Attendance::updateOrCreate(
            [
                'student_id' => $studentId,
                'attendance_date' => $date,
            ],
            [
                'class_id' => $classId,
                'section_id' => $sectionId,
                'attendance_type_id' => $attendanceTypeId,
                'marked_by' => $markedBy,
                'remarks' => $remarks,
            ]
        );
    }

    /**
     * Mark bulk attendance for a class/section.
     * 
     * @param int $classId
     * @param int $sectionId
     * @param string $date
     * @param array $attendanceData Array of ['student_id' => attendance_type_id]
     * @param int|null $markedBy
     * @return int Number of records created/updated
     */
    public function markBulkAttendance(
        int $classId,
        int $sectionId,
        string $date,
        array $attendanceData,
        ?int $markedBy = null
    ): int {
        $count = 0;
        
        DB::transaction(function () use ($classId, $sectionId, $date, $attendanceData, $markedBy, &$count) {
            foreach ($attendanceData as $studentId => $attendanceTypeId) {
                $this->markAttendance(
                    $studentId,
                    $classId,
                    $sectionId,
                    $date,
                    $attendanceTypeId,
                    $markedBy
                );
                $count++;
            }
        });
        
        return $count;
    }

    /**
     * Check if attendance is already marked for a class/section on a date.
     * 
     * @param int $classId
     * @param int $sectionId
     * @param string $date
     * @return bool
     */
    public function isAttendanceMarked(int $classId, int $sectionId, string $date): bool
    {
        return Attendance::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereDate('attendance_date', $date)
            ->exists();
    }

    /**
     * Get attendance for a class/section on a date.
     * 
     * @param int $classId
     * @param int $sectionId
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAttendance(int $classId, int $sectionId, string $date)
    {
        return Attendance::with(['student.user', 'attendanceType'])
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereDate('attendance_date', $date)
            ->get();
    }

    /**
     * Get student attendance for a date range.
     * 
     * @param int $studentId
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStudentAttendance(int $studentId, string $startDate, string $endDate)
    {
        return Attendance::with('attendanceType')
            ->where('student_id', $studentId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date')
            ->get();
    }

    /**
     * Get monthly attendance summary for a student.
     * 
     * @param int $studentId
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getStudentMonthlySummary(int $studentId, int $month, int $year): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $attendances = Attendance::with('attendanceType')
            ->where('student_id', $studentId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();
        
        $summary = [
            'total_days' => $attendances->count(),
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'leave' => 0,
            'half_day' => 0,
        ];
        
        foreach ($attendances as $attendance) {
            $typeName = strtolower($attendance->attendanceType->name ?? 'absent');
            if (isset($summary[$typeName])) {
                $summary[$typeName]++;
            }
        }
        
        $summary['percentage'] = $summary['total_days'] > 0 
            ? round(($summary['present'] / $summary['total_days']) * 100, 2) 
            : 0;
        
        return $summary;
    }

    /**
     * Get class/section attendance summary for a date.
     * 
     * @param int $classId
     * @param int $sectionId
     * @param string $date
     * @return array
     */
    public function getClassSummary(int $classId, int $sectionId, string $date): array
    {
        $attendances = Attendance::with('attendanceType')
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereDate('attendance_date', $date)
            ->get();
        
        $totalStudents = Student::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('is_active', true)
            ->count();
        
        $summary = [
            'total_students' => $totalStudents,
            'marked' => $attendances->count(),
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'leave' => 0,
            'half_day' => 0,
        ];
        
        foreach ($attendances as $attendance) {
            $typeName = strtolower($attendance->attendanceType->name ?? 'absent');
            if (isset($summary[$typeName])) {
                $summary[$typeName]++;
            }
        }
        
        $summary['not_marked'] = $totalStudents - $summary['marked'];
        $summary['percentage'] = $summary['marked'] > 0 
            ? round(($summary['present'] / $summary['marked']) * 100, 2) 
            : 0;
        
        return $summary;
    }

    /**
     * Get monthly attendance summary for a class/section.
     * 
     * @param int $classId
     * @param int $sectionId
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getClassMonthlySummary(int $classId, int $sectionId, int $month, int $year): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $students = Student::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('is_active', true)
            ->get();
        
        $summary = [];
        
        foreach ($students as $student) {
            $studentSummary = $this->getStudentMonthlySummary($student->id, $month, $year);
            $summary[] = [
                'student_id' => $student->id,
                'student_name' => $student->user->full_name ?? '',
                'admission_number' => $student->admission_number,
                'summary' => $studentSummary,
            ];
        }
        
        return $summary;
    }

    /**
     * Get attendance types.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAttendanceTypes()
    {
        return AttendanceType::where('is_active', true)->orderBy('name')->get();
    }

    /**
     * Get attendance report data.
     * 
     * @param array $filters
     * @return array
     */
    public function getReportData(array $filters): array
    {
        $query = Attendance::with(['student.user', 'schoolClass', 'section', 'attendanceType']);
        
        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }
        
        if (!empty($filters['section_id'])) {
            $query->where('section_id', $filters['section_id']);
        }
        
        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }
        
        if (!empty($filters['start_date'])) {
            $query->whereDate('attendance_date', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->whereDate('attendance_date', '<=', $filters['end_date']);
        }
        
        if (!empty($filters['attendance_type_id'])) {
            $query->where('attendance_type_id', $filters['attendance_type_id']);
        }
        
        $attendances = $query->orderBy('attendance_date', 'desc')->get();
        
        return [
            'data' => $attendances,
            'summary' => [
                'total' => $attendances->count(),
                'present' => $attendances->filter(fn($a) => strtolower($a->attendanceType->name ?? '') === 'present')->count(),
                'absent' => $attendances->filter(fn($a) => strtolower($a->attendanceType->name ?? '') === 'absent')->count(),
                'late' => $attendances->filter(fn($a) => strtolower($a->attendanceType->name ?? '') === 'late')->count(),
            ],
        ];
    }

    /**
     * Get today's attendance statistics.
     * 
     * @return array
     */
    public function getTodayStatistics(): array
    {
        $today = now()->format('Y-m-d');
        
        $attendances = Attendance::with('attendanceType')
            ->whereDate('attendance_date', $today)
            ->get();
        
        $totalStudents = Student::where('is_active', true)->count();
        
        return [
            'date' => $today,
            'total_students' => $totalStudents,
            'marked' => $attendances->count(),
            'present' => $attendances->filter(fn($a) => strtolower($a->attendanceType->name ?? '') === 'present')->count(),
            'absent' => $attendances->filter(fn($a) => strtolower($a->attendanceType->name ?? '') === 'absent')->count(),
            'late' => $attendances->filter(fn($a) => strtolower($a->attendanceType->name ?? '') === 'late')->count(),
            'not_marked' => $totalStudents - $attendances->count(),
        ];
    }
}
