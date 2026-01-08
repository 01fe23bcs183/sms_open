<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Dropdown Controller
 * 
 * Prompt 298: Add Dependent Dropdown Endpoints
 * 
 * Provides endpoints for Select2 and cascading dropdown components.
 * Returns data in Select2-compatible format with pagination support.
 */
class DropdownController extends Controller
{
    /**
     * Cache TTL in seconds (5 minutes)
     */
    protected const CACHE_TTL = 300;

    /**
     * Get classes dropdown.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function classes(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $sessionId = $request->input('session_id');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 25);

        $cacheKey = "dropdown:classes:{$sessionId}:{$search}:{$page}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($search, $sessionId, $page, $perPage) {
            $query = DB::table('classes')
                ->select('id', 'name', 'numeric_name', 'is_active')
                ->where('is_active', true)
                ->whereNull('deleted_at');

            if ($sessionId) {
                $query->where('academic_session_id', $sessionId);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('numeric_name', 'like', "%{$search}%");
                });
            }

            $query->orderBy('numeric_name')->orderBy('name');

            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        return $this->dropdownResponse(
            $data->items(),
            $data->hasMorePages(),
            'name'
        );
    }

    /**
     * Get sections dropdown (dependent on class).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sections(Request $request): JsonResponse
    {
        $classId = $request->input('class_id');
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 25);

        if (!$classId) {
            return $this->dropdownResponse([], false);
        }

        $cacheKey = "dropdown:sections:{$classId}:{$search}:{$page}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($classId, $search, $page, $perPage) {
            $query = DB::table('sections')
                ->select('id', 'name', 'capacity', 'is_active')
                ->where('class_id', $classId)
                ->where('is_active', true)
                ->whereNull('deleted_at');

            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            $query->orderBy('name');

            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        return $this->dropdownResponse(
            $data->items(),
            $data->hasMorePages(),
            'name'
        );
    }

    /**
     * Get subjects dropdown (optionally dependent on class).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function subjects(Request $request): JsonResponse
    {
        $classId = $request->input('class_id');
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 25);

        $cacheKey = "dropdown:subjects:{$classId}:{$search}:{$page}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($classId, $search, $page, $perPage) {
            $query = DB::table('subjects')
                ->select('id', 'name', 'code', 'type', 'is_active')
                ->where('is_active', true)
                ->whereNull('deleted_at');

            if ($classId) {
                $query->whereIn('id', function ($subQuery) use ($classId) {
                    $subQuery->select('subject_id')
                        ->from('class_subjects')
                        ->where('class_id', $classId);
                });
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            $query->orderBy('name');

            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        return $this->dropdownResponse(
            $data->items(),
            $data->hasMorePages(),
            'name',
            ['code']
        );
    }

    /**
     * Get students dropdown (dependent on class and section).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function students(Request $request): JsonResponse
    {
        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $sessionId = $request->input('session_id');
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 25);

        $cacheKey = "dropdown:students:{$sessionId}:{$classId}:{$sectionId}:{$search}:{$page}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($classId, $sectionId, $sessionId, $search, $page, $perPage) {
            $query = DB::table('students')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->select(
                    'students.id',
                    'students.admission_number',
                    'students.roll_number',
                    'users.name'
                )
                ->where('students.is_active', true)
                ->whereNull('students.deleted_at');

            if ($sessionId) {
                $query->where('students.academic_session_id', $sessionId);
            }

            if ($classId) {
                $query->where('students.class_id', $classId);
            }

            if ($sectionId) {
                $query->where('students.section_id', $sectionId);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('users.name', 'like', "%{$search}%")
                      ->orWhere('students.admission_number', 'like', "%{$search}%")
                      ->orWhere('students.roll_number', 'like', "%{$search}%");
                });
            }

            $query->orderBy('students.roll_number')->orderBy('users.name');

            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        // Format for Select2 with custom text
        $items = collect($data->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => "{$item->name} ({$item->admission_number})",
                'admission_number' => $item->admission_number,
                'roll_number' => $item->roll_number,
            ];
        });

        return response()->json([
            'results' => $items,
            'pagination' => [
                'more' => $data->hasMorePages(),
            ],
        ]);
    }

    /**
     * Get teachers dropdown.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function teachers(Request $request): JsonResponse
    {
        $subjectId = $request->input('subject_id');
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 25);

        $cacheKey = "dropdown:teachers:{$subjectId}:{$search}:{$page}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($subjectId, $search, $page, $perPage) {
            $query = DB::table('users')
                ->join('model_has_roles', function ($join) {
                    $join->on('users.id', '=', 'model_has_roles.model_id')
                         ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
                })
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('users.id', 'users.name', 'users.email', 'users.employee_id')
                ->where('roles.name', 'teacher')
                ->whereNull('users.deleted_at');

            if ($subjectId) {
                $query->whereIn('users.id', function ($subQuery) use ($subjectId) {
                    $subQuery->select('teacher_id')
                        ->from('class_subjects')
                        ->where('subject_id', $subjectId);
                });
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('users.name', 'like', "%{$search}%")
                      ->orWhere('users.email', 'like', "%{$search}%")
                      ->orWhere('users.employee_id', 'like', "%{$search}%");
                });
            }

            $query->orderBy('users.name');

            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        return $this->dropdownResponse(
            $data->items(),
            $data->hasMorePages(),
            'name',
            ['email', 'employee_id']
        );
    }

    /**
     * Get academic sessions dropdown.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function academicSessions(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 25);

        $cacheKey = "dropdown:academic_sessions:{$search}:{$page}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($search, $page, $perPage) {
            $query = DB::table('academic_sessions')
                ->select('id', 'name', 'start_date', 'end_date', 'is_current', 'is_active')
                ->where('is_active', true)
                ->whereNull('deleted_at');

            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            $query->orderByDesc('is_current')->orderByDesc('start_date');

            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        return $this->dropdownResponse(
            $data->items(),
            $data->hasMorePages(),
            'name',
            ['is_current']
        );
    }

    /**
     * Get exam types dropdown.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function examTypes(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 25);

        $cacheKey = "dropdown:exam_types:{$search}:{$page}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($search, $page, $perPage) {
            $query = DB::table('exam_types')
                ->select('id', 'name', 'code', 'is_active')
                ->where('is_active', true)
                ->whereNull('deleted_at');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            $query->orderBy('name');

            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        return $this->dropdownResponse(
            $data->items(),
            $data->hasMorePages(),
            'name',
            ['code']
        );
    }

    /**
     * Get exams dropdown (dependent on exam type and session).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function exams(Request $request): JsonResponse
    {
        $examTypeId = $request->input('exam_type_id');
        $sessionId = $request->input('session_id');
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 25);

        $cacheKey = "dropdown:exams:{$examTypeId}:{$sessionId}:{$search}:{$page}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($examTypeId, $sessionId, $search, $page, $perPage) {
            $query = DB::table('exams')
                ->select('id', 'name', 'start_date', 'end_date', 'is_active')
                ->where('is_active', true)
                ->whereNull('deleted_at');

            if ($examTypeId) {
                $query->where('exam_type_id', $examTypeId);
            }

            if ($sessionId) {
                $query->where('academic_session_id', $sessionId);
            }

            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            $query->orderByDesc('start_date');

            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        return $this->dropdownResponse(
            $data->items(),
            $data->hasMorePages(),
            'name'
        );
    }

    /**
     * Get fees types dropdown.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function feesTypes(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 25);

        $cacheKey = "dropdown:fees_types:{$search}:{$page}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($search, $page, $perPage) {
            $query = DB::table('fees_types')
                ->select('id', 'name', 'code', 'is_active')
                ->where('is_active', true)
                ->whereNull('deleted_at');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            $query->orderBy('name');

            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        return $this->dropdownResponse(
            $data->items(),
            $data->hasMorePages(),
            'name',
            ['code']
        );
    }

    /**
     * Get fees groups dropdown.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function feesGroups(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 25);

        $cacheKey = "dropdown:fees_groups:{$search}:{$page}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($search, $page, $perPage) {
            $query = DB::table('fees_groups')
                ->select('id', 'name', 'description', 'is_active')
                ->where('is_active', true)
                ->whereNull('deleted_at');

            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            $query->orderBy('name');

            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        return $this->dropdownResponse(
            $data->items(),
            $data->hasMorePages(),
            'name'
        );
    }

    /**
     * Get attendance types dropdown.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function attendanceTypes(Request $request): JsonResponse
    {
        $search = $request->input('search', '');

        $cacheKey = "dropdown:attendance_types:{$search}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($search) {
            $query = DB::table('attendance_types')
                ->select('id', 'name', 'code', 'color', 'is_present', 'is_active')
                ->where('is_active', true)
                ->whereNull('deleted_at');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            $query->orderBy('name');

            return $query->get();
        });

        return $this->dropdownResponse(
            $data->toArray(),
            false,
            'name',
            ['code', 'color', 'is_present']
        );
    }

    /**
     * Get student categories dropdown.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function studentCategories(Request $request): JsonResponse
    {
        $search = $request->input('search', '');

        $cacheKey = "dropdown:student_categories:{$search}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($search) {
            $query = DB::table('student_categories')
                ->select('id', 'name', 'description', 'is_active')
                ->where('is_active', true)
                ->whereNull('deleted_at');

            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            $query->orderBy('name');

            return $query->get();
        });

        return $this->dropdownResponse(
            $data->toArray(),
            false,
            'name'
        );
    }

    /**
     * Get library categories dropdown.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function libraryCategories(Request $request): JsonResponse
    {
        $search = $request->input('search', '');

        $cacheKey = "dropdown:library_categories:{$search}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($search) {
            $query = DB::table('library_categories')
                ->select('id', 'name', 'code', 'is_active')
                ->where('is_active', true)
                ->whereNull('deleted_at');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            $query->orderBy('name');

            return $query->get();
        });

        return $this->dropdownResponse(
            $data->toArray(),
            false,
            'name',
            ['code']
        );
    }

    /**
     * Get transport routes dropdown.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function transportRoutes(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 25);

        $cacheKey = "dropdown:transport_routes:{$search}:{$page}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($search, $page, $perPage) {
            $query = DB::table('transport_routes')
                ->select('id', 'name', 'route_number', 'start_place', 'end_place', 'fare', 'is_active')
                ->where('is_active', true)
                ->whereNull('deleted_at');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('route_number', 'like', "%{$search}%")
                      ->orWhere('start_place', 'like', "%{$search}%")
                      ->orWhere('end_place', 'like', "%{$search}%");
                });
            }

            $query->orderBy('route_number');

            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        // Format with route details
        $items = collect($data->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => "{$item->name} ({$item->start_place} - {$item->end_place})",
                'route_number' => $item->route_number,
                'fare' => $item->fare,
            ];
        });

        return response()->json([
            'results' => $items,
            'pagination' => [
                'more' => $data->hasMorePages(),
            ],
        ]);
    }

    /**
     * Get transport stops dropdown (dependent on route).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function transportStops(Request $request): JsonResponse
    {
        $routeId = $request->input('route_id');
        $search = $request->input('search', '');

        if (!$routeId) {
            return $this->dropdownResponse([], false);
        }

        $cacheKey = "dropdown:transport_stops:{$routeId}:{$search}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($routeId, $search) {
            $query = DB::table('transport_route_stops')
                ->select('id', 'stop_name', 'stop_order', 'stop_time', 'fare')
                ->where('route_id', $routeId)
                ->whereNull('deleted_at');

            if ($search) {
                $query->where('stop_name', 'like', "%{$search}%");
            }

            $query->orderBy('stop_order');

            return $query->get();
        });

        // Format with stop details
        $items = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => "{$item->stop_name} ({$item->stop_time})",
                'stop_order' => $item->stop_order,
                'fare' => $item->fare,
            ];
        });

        return response()->json([
            'results' => $items,
            'pagination' => [
                'more' => false,
            ],
        ]);
    }

    /**
     * Get vehicles dropdown.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function vehicles(Request $request): JsonResponse
    {
        $routeId = $request->input('route_id');
        $search = $request->input('search', '');

        $cacheKey = "dropdown:vehicles:{$routeId}:{$search}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($routeId, $search) {
            $query = DB::table('transport_vehicles')
                ->select('id', 'vehicle_number', 'vehicle_model', 'driver_name', 'capacity', 'is_active')
                ->where('is_active', true)
                ->whereNull('deleted_at');

            if ($routeId) {
                $query->where('route_id', $routeId);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('vehicle_number', 'like', "%{$search}%")
                      ->orWhere('vehicle_model', 'like', "%{$search}%")
                      ->orWhere('driver_name', 'like', "%{$search}%");
                });
            }

            $query->orderBy('vehicle_number');

            return $query->get();
        });

        // Format with vehicle details
        $items = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => "{$item->vehicle_number} - {$item->vehicle_model}",
                'driver_name' => $item->driver_name,
                'capacity' => $item->capacity,
            ];
        });

        return response()->json([
            'results' => $items,
            'pagination' => [
                'more' => false,
            ],
        ]);
    }

    /**
     * Get books dropdown.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function books(Request $request): JsonResponse
    {
        $categoryId = $request->input('category_id');
        $availableOnly = $request->boolean('available_only', false);
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 25);

        $cacheKey = "dropdown:books:{$categoryId}:{$availableOnly}:{$search}:{$page}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($categoryId, $availableOnly, $search, $page, $perPage) {
            $query = DB::table('library_books')
                ->select('id', 'title', 'isbn', 'author', 'available_quantity', 'is_active')
                ->where('is_active', true)
                ->whereNull('deleted_at');

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            if ($availableOnly) {
                $query->where('available_quantity', '>', 0);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('isbn', 'like', "%{$search}%")
                      ->orWhere('author', 'like', "%{$search}%");
                });
            }

            $query->orderBy('title');

            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        // Format with book details
        $items = collect($data->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => "{$item->title} by {$item->author}",
                'isbn' => $item->isbn,
                'available_quantity' => $item->available_quantity,
            ];
        });

        return response()->json([
            'results' => $items,
            'pagination' => [
                'more' => $data->hasMorePages(),
            ],
        ]);
    }

    /**
     * Get library members dropdown.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function libraryMembers(Request $request): JsonResponse
    {
        $memberType = $request->input('member_type');
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 25);

        $cacheKey = "dropdown:library_members:{$memberType}:{$search}:{$page}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($memberType, $search, $page, $perPage) {
            $query = DB::table('library_members')
                ->select('id', 'membership_number', 'member_type', 'member_id', 'is_active')
                ->where('is_active', true)
                ->whereNull('deleted_at');

            if ($memberType) {
                $query->where('member_type', $memberType);
            }

            if ($search) {
                $query->where('membership_number', 'like', "%{$search}%");
            }

            $query->orderBy('membership_number');

            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        return $this->dropdownResponse(
            $data->items(),
            $data->hasMorePages(),
            'membership_number',
            ['member_type']
        );
    }
}
