<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentCollection;
use App\Http\Resources\StudentResource;
use App\Traits\HasDataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Student API Controller
 * 
 * Prompt 299: Add Server-Side Pagination, Search, and Filters
 * 
 * Provides API endpoints for student data with pagination,
 * search, and filter support for DataTables.
 */
class StudentController extends Controller
{
    use HasDataTables;

    /**
     * Get paginated list of students.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('classes', 'students.class_id', '=', 'classes.id')
            ->leftJoin('sections', 'students.section_id', '=', 'sections.id')
            ->select([
                'students.id',
                'students.admission_number',
                'students.roll_number',
                'students.date_of_admission',
                'students.date_of_birth',
                'students.gender',
                'students.is_active',
                'students.created_at',
                'users.name',
                'users.email',
                'users.phone',
                'classes.name as class_name',
                'sections.name as section_name',
            ])
            ->whereNull('students.deleted_at');

        // Apply filters
        if ($request->filled('academic_session_id')) {
            $query->where('students.academic_session_id', $request->input('academic_session_id'));
        }

        if ($request->filled('class_id')) {
            $query->where('students.class_id', $request->input('class_id'));
        }

        if ($request->filled('section_id')) {
            $query->where('students.section_id', $request->input('section_id'));
        }

        if ($request->filled('gender')) {
            $query->where('students.gender', $request->input('gender'));
        }

        if ($request->filled('is_active')) {
            $query->where('students.is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        // Check if DataTables request
        if ($request->has('draw')) {
            return $this->simpleDataTablesResponse(
                $query,
                $request,
                ['users.name', 'students.admission_number', 'students.roll_number', 'users.email'],
                ['users.name', 'students.admission_number', 'students.roll_number', 'students.created_at'],
                function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->name,
                        'email' => $student->email,
                        'phone' => $student->phone,
                        'admission_number' => $student->admission_number,
                        'roll_number' => $student->roll_number,
                        'class' => $student->class_name,
                        'section' => $student->section_name,
                        'gender' => ucfirst($student->gender),
                        'is_active' => $student->is_active,
                        'date_of_admission' => $student->date_of_admission,
                        'created_at' => $student->created_at,
                    ];
                }
            );
        }

        // Standard pagination response
        $perPage = min($request->input('per_page', 15), 100);
        $students = $query->orderBy('students.created_at', 'desc')->paginate($perPage);

        return $this->paginatedResponse(
            $students->items(),
            $students->total(),
            $students->currentPage(),
            $students->perPage(),
            'Students retrieved successfully'
        );
    }

    /**
     * Get a single student.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $student = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('classes', 'students.class_id', '=', 'classes.id')
            ->leftJoin('sections', 'students.section_id', '=', 'sections.id')
            ->leftJoin('academic_sessions', 'students.academic_session_id', '=', 'academic_sessions.id')
            ->select([
                'students.*',
                'users.name',
                'users.email',
                'users.phone',
                'classes.name as class_name',
                'sections.name as section_name',
                'academic_sessions.name as session_name',
            ])
            ->where('students.id', $id)
            ->whereNull('students.deleted_at')
            ->first();

        if (!$student) {
            return $this->notFoundResponse('Student not found');
        }

        return $this->successResponse($student, 'Student retrieved successfully');
    }

    /**
     * Search students.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        $limit = min($request->input('limit', 10), 50);

        if (strlen($search) < 2) {
            return $this->successResponse([], 'Search query too short');
        }

        $students = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('classes', 'students.class_id', '=', 'classes.id')
            ->leftJoin('sections', 'students.section_id', '=', 'sections.id')
            ->select([
                'students.id',
                'students.admission_number',
                'students.roll_number',
                'users.name',
                'classes.name as class_name',
                'sections.name as section_name',
            ])
            ->where('students.is_active', true)
            ->whereNull('students.deleted_at')
            ->where(function ($query) use ($search) {
                $query->where('users.name', 'like', "%{$search}%")
                    ->orWhere('students.admission_number', 'like', "%{$search}%")
                    ->orWhere('students.roll_number', 'like', "%{$search}%");
            })
            ->orderBy('users.name')
            ->limit($limit)
            ->get();

        return $this->successResponse($students, 'Search results');
    }

    /**
     * Get student statistics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        $sessionId = $request->input('academic_session_id');

        $query = DB::table('students')->whereNull('deleted_at');

        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }

        $totalStudents = $query->count();
        $activeStudents = (clone $query)->where('is_active', true)->count();
        $inactiveStudents = (clone $query)->where('is_active', false)->count();

        $genderStats = (clone $query)
            ->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();

        $classStats = DB::table('students')
            ->join('classes', 'students.class_id', '=', 'classes.id')
            ->whereNull('students.deleted_at')
            ->when($sessionId, fn($q) => $q->where('students.academic_session_id', $sessionId))
            ->select('classes.name', DB::raw('count(*) as count'))
            ->groupBy('classes.id', 'classes.name')
            ->orderBy('classes.numeric_name')
            ->get();

        return $this->successResponse([
            'total' => $totalStudents,
            'active' => $activeStudents,
            'inactive' => $inactiveStudents,
            'by_gender' => $genderStats,
            'by_class' => $classStats,
        ], 'Student statistics');
    }
}
