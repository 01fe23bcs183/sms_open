<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * API Report Controller
 * 
 * Prompt 304: Add Report Export Endpoints with Filters
 * 
 * Handles report API endpoints and exports to PDF, Excel, CSV.
 */
class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private ExportService $exportService
    ) {}

    /**
     * Get students report data.
     * 
     * GET /api/v1/reports/students
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function students(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request, [
            'class_id', 'section_id', 'academic_session_id', 
            'status', 'gender', 'date_from', 'date_to'
        ]);

        $data = $this->reportService->getStudentsReport($filters);

        return $this->successResponse(
            $data,
            'Students report retrieved successfully',
            ['total' => $data->count()]
        );
    }

    /**
     * Get attendance report data.
     * 
     * GET /api/v1/reports/attendance
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function attendance(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request, [
            'class_id', 'section_id', 'student_id', 
            'status', 'date_from', 'date_to'
        ]);

        $data = $this->reportService->getAttendanceReport($filters);

        return $this->successResponse(
            $data,
            'Attendance report retrieved successfully',
            ['total' => $data->count()]
        );
    }

    /**
     * Get fees report data.
     * 
     * GET /api/v1/reports/fees
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function fees(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request, [
            'student_id', 'fees_type_id', 'payment_status', 
            'payment_method', 'date_from', 'date_to'
        ]);

        $data = $this->reportService->getFeesReport($filters);

        return $this->successResponse(
            $data,
            'Fees report retrieved successfully',
            ['total' => $data->count()]
        );
    }

    /**
     * Get exams report data.
     * 
     * GET /api/v1/reports/exams
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function exams(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request, [
            'student_id', 'exam_id', 'class_id', 'subject_id'
        ]);

        $data = $this->reportService->getExamsReport($filters);

        return $this->successResponse(
            $data,
            'Exams report retrieved successfully',
            ['total' => $data->count()]
        );
    }

    /**
     * Export report data to specified format.
     * 
     * POST /api/v1/reports/export
     * 
     * Request body:
     * {
     *   "module": "students|attendance|fees|exams",
     *   "format": "pdf|xlsx|csv",
     *   "filters": { ... }
     * }
     * 
     * @param Request $request
     * @return Response|StreamedResponse|JsonResponse
     */
    public function export(Request $request): Response|StreamedResponse|JsonResponse
    {
        $request->validate([
            'module' => 'required|string|in:students,attendance,fees,exams',
            'format' => 'required|string|in:pdf,xlsx,csv',
            'filters' => 'nullable|array',
        ]);

        $module = $request->input('module');
        $format = $request->input('format');
        $filters = $request->input('filters', []);

        $data = $this->reportService->getReportData($module, $filters);

        if ($data->isEmpty()) {
            return $this->errorResponse('No data found for the specified filters');
        }

        $filename = $this->generateFilename($module);
        $title = $this->getReportTitle($module);

        try {
            return $this->exportService->export($data, $format, $filename, $title);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Export failed: ' . $e->getMessage(),
                [],
                500
            );
        }
    }

    /**
     * Get available modules and formats for export.
     * 
     * GET /api/v1/reports/options
     * 
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        return $this->successResponse([
            'modules' => $this->reportService->getAvailableModules(),
            'formats' => $this->exportService->getSupportedFormats(),
        ], 'Export options retrieved successfully');
    }

    /**
     * Extract filters from request.
     * 
     * @param Request $request
     * @param array $allowedFilters
     * @return array
     */
    private function extractFilters(Request $request, array $allowedFilters): array
    {
        $filters = [];

        foreach ($allowedFilters as $filter) {
            if ($request->has($filter)) {
                $filters[$filter] = $request->input($filter);
            }
        }

        return $filters;
    }

    /**
     * Generate filename for export.
     * 
     * @param string $module
     * @return string
     */
    private function generateFilename(string $module): string
    {
        $timestamp = now()->format('Y-m-d_His');
        return "{$module}_report_{$timestamp}";
    }

    /**
     * Get report title by module.
     * 
     * @param string $module
     * @return string
     */
    private function getReportTitle(string $module): string
    {
        $titles = [
            'students' => 'Students Report',
            'attendance' => 'Attendance Report',
            'fees' => 'Fees Collection Report',
            'exams' => 'Examination Results Report',
        ];

        return $titles[$module] ?? ucfirst($module) . ' Report';
    }
}
