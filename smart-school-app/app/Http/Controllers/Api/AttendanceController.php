<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasDataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Attendance API Controller
 * 
 * Prompt 299: Add Server-Side Pagination, Search, and Filters
 * 
 * Provides API endpoints for attendance data with pagination,
 * search, and filter support.
 */
class AttendanceController extends Controller
{
    use HasDataTables;

    /**
     * Get attendance records.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = DB::table('attendances')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('attendance_types', 'attendances.attendance_type_id', '=', 'attendance_types.id')
            ->leftJoin('classes', 'attendances.class_id', '=', 'classes.id')
            ->leftJoin('sections', 'attendances.section_id', '=', 'sections.id')
            ->select([
                'attendances.id',
                'attendances.date',
                'attendances.remarks',
                'attendances.created_at',
                'students.admission_number',
                'students.roll_number',
                'users.name as student_name',
                'attendance_types.name as attendance_type',
                'attendance_types.code as attendance_code',
                'attendance_types.color as attendance_color',
                'attendance_types.is_present',
                'classes.name as class_name',
                'sections.name as section_name',
            ])
            ->whereNull('attendances.deleted_at');

        // Apply filters
        if ($request->filled('academic_session_id')) {
            $query->where('attendances.academic_session_id', $request->input('academic_session_id'));
        }

        if ($request->filled('class_id')) {
            $query->where('attendances.class_id', $request->input('class_id'));
        }

        if ($request->filled('section_id')) {
            $query->where('attendances.section_id', $request->input('section_id'));
        }

        if ($request->filled('student_id')) {
            $query->where('attendances.student_id', $request->input('student_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('attendances.date', $request->input('date'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('attendances.date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('attendances.date', '<=', $request->input('date_to'));
        }

        if ($request->filled('attendance_type_id')) {
            $query->where('attendances.attendance_type_id', $request->input('attendance_type_id'));
        }

        // Check if DataTables request
        if ($request->has('draw')) {
            return $this->simpleDataTablesResponse(
                $query,
                $request,
                ['users.name', 'students.admission_number', 'students.roll_number'],
                ['attendances.date', 'users.name', 'students.roll_number'],
                function ($attendance) {
                    return [
                        'id' => $attendance->id,
                        'date' => $attendance->date,
                        'student_name' => $attendance->student_name,
                        'admission_number' => $attendance->admission_number,
                        'roll_number' => $attendance->roll_number,
                        'class' => $attendance->class_name,
                        'section' => $attendance->section_name,
                        'attendance_type' => $attendance->attendance_type,
                        'attendance_code' => $attendance->attendance_code,
                        'attendance_color' => $attendance->attendance_color,
                        'is_present' => $attendance->is_present,
                        'remarks' => $attendance->remarks,
                    ];
                }
            );
        }

        // Standard pagination response
        $perPage = min($request->input('per_page', 15), 100);
        $attendances = $query->orderBy('attendances.date', 'desc')->paginate($perPage);

        return $this->paginatedResponse(
            $attendances->items(),
            $attendances->total(),
            $attendances->currentPage(),
            $attendances->perPage(),
            'Attendance records retrieved successfully'
        );
    }

    /**
     * Get attendance for a specific date and class/section.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function byDate(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $date = $request->input('date');
        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $sessionId = $request->input('academic_session_id');

        // Get all students in the class/section
        $students = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('attendances', function ($join) use ($date) {
                $join->on('students.id', '=', 'attendances.student_id')
                    ->whereDate('attendances.date', $date)
                    ->whereNull('attendances.deleted_at');
            })
            ->leftJoin('attendance_types', 'attendances.attendance_type_id', '=', 'attendance_types.id')
            ->select([
                'students.id',
                'students.admission_number',
                'students.roll_number',
                'users.name',
                'attendances.id as attendance_id',
                'attendances.attendance_type_id',
                'attendance_types.name as attendance_type',
                'attendance_types.code as attendance_code',
                'attendance_types.color as attendance_color',
                'attendance_types.is_present',
                'attendances.remarks',
            ])
            ->where('students.class_id', $classId)
            ->where('students.section_id', $sectionId)
            ->where('students.is_active', true)
            ->whereNull('students.deleted_at')
            ->when($sessionId, fn($q) => $q->where('students.academic_session_id', $sessionId))
            ->orderBy('students.roll_number')
            ->orderBy('users.name')
            ->get();

        return $this->successResponse([
            'date' => $date,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'students' => $students,
            'total_students' => $students->count(),
            'marked_count' => $students->whereNotNull('attendance_id')->count(),
            'present_count' => $students->where('is_present', true)->count(),
            'absent_count' => $students->where('is_present', false)->whereNotNull('attendance_id')->count(),
        ], 'Attendance data retrieved');
    }

    /**
     * Get attendance statistics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        $sessionId = $request->input('academic_session_id');
        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $studentId = $request->input('student_id');
        $month = $request->input('month'); // Format: YYYY-MM

        $query = DB::table('attendances')
            ->join('attendance_types', 'attendances.attendance_type_id', '=', 'attendance_types.id')
            ->whereNull('attendances.deleted_at');

        if ($sessionId) {
            $query->where('attendances.academic_session_id', $sessionId);
        }

        if ($classId) {
            $query->where('attendances.class_id', $classId);
        }

        if ($sectionId) {
            $query->where('attendances.section_id', $sectionId);
        }

        if ($studentId) {
            $query->where('attendances.student_id', $studentId);
        }

        if ($month) {
            $query->whereRaw("DATE_FORMAT(attendances.date, '%Y-%m') = ?", [$month]);
        }

        $stats = $query
            ->select([
                'attendance_types.name',
                'attendance_types.code',
                'attendance_types.color',
                'attendance_types.is_present',
                DB::raw('count(*) as count'),
            ])
            ->groupBy('attendance_types.id', 'attendance_types.name', 'attendance_types.code', 'attendance_types.color', 'attendance_types.is_present')
            ->get();

        $totalRecords = $stats->sum('count');
        $presentCount = $stats->where('is_present', true)->sum('count');
        $absentCount = $stats->where('is_present', false)->sum('count');

        return $this->successResponse([
            'total_records' => $totalRecords,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'attendance_percentage' => $totalRecords > 0 
                ? round(($presentCount / $totalRecords) * 100, 2) 
                : 0,
            'by_type' => $stats,
        ], 'Attendance statistics');
    }

    /**
     * Get monthly attendance report.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function monthlyReport(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $month = $request->input('month');
        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $sessionId = $request->input('academic_session_id');

        // Get all students
        $students = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->select([
                'students.id',
                'students.admission_number',
                'students.roll_number',
                'users.name',
            ])
            ->where('students.class_id', $classId)
            ->where('students.section_id', $sectionId)
            ->where('students.is_active', true)
            ->whereNull('students.deleted_at')
            ->when($sessionId, fn($q) => $q->where('students.academic_session_id', $sessionId))
            ->orderBy('students.roll_number')
            ->get();

        // Get attendance for the month
        $attendances = DB::table('attendances')
            ->join('attendance_types', 'attendances.attendance_type_id', '=', 'attendance_types.id')
            ->select([
                'attendances.student_id',
                'attendances.date',
                'attendance_types.code',
                'attendance_types.is_present',
            ])
            ->where('attendances.class_id', $classId)
            ->where('attendances.section_id', $sectionId)
            ->whereRaw("DATE_FORMAT(attendances.date, '%Y-%m') = ?", [$month])
            ->whereNull('attendances.deleted_at')
            ->get()
            ->groupBy('student_id');

        // Build report
        $report = $students->map(function ($student) use ($attendances) {
            $studentAttendance = $attendances->get($student->id, collect());
            $presentDays = $studentAttendance->where('is_present', true)->count();
            $absentDays = $studentAttendance->where('is_present', false)->count();
            $totalDays = $studentAttendance->count();

            return [
                'student_id' => $student->id,
                'name' => $student->name,
                'admission_number' => $student->admission_number,
                'roll_number' => $student->roll_number,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'total_days' => $totalDays,
                'attendance_percentage' => $totalDays > 0 
                    ? round(($presentDays / $totalDays) * 100, 2) 
                    : 0,
                'daily_attendance' => $studentAttendance->keyBy('date')->map(fn($a) => $a->code),
            ];
        });

        return $this->successResponse([
            'month' => $month,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'students' => $report,
        ], 'Monthly attendance report');
    }
}
