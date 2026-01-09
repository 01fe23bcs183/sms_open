<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * API Dashboard Controller
 * 
 * Prompt 303: Provide Dashboard Metrics and Chart Data Endpoints
 * 
 * Handles dashboard metrics and chart data API endpoints.
 * Uses DashboardService for aggregations and caching.
 */
class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Get aggregated dashboard metrics.
     * 
     * GET /api/v1/dashboard/metrics
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function metrics(Request $request): JsonResponse
    {
        $metrics = $this->dashboardService->getMetrics();

        return $this->successResponse(
            $metrics,
            'Dashboard metrics retrieved successfully'
        );
    }

    /**
     * Get attendance chart data for Chart.js.
     * 
     * GET /api/v1/dashboard/charts/attendance?period=monthly&year=2026
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function attendanceChart(Request $request): JsonResponse
    {
        $period = $request->get('period', 'monthly');
        $year = $request->integer('year', now()->year);

        if (!in_array($period, ['monthly', 'weekly'])) {
            return $this->errorResponse('Invalid period. Use "monthly" or "weekly".');
        }

        $chartData = $this->dashboardService->getAttendanceChartData($period, $year);

        return $this->chartResponse($chartData['labels'], $chartData['datasets']);
    }

    /**
     * Get fees chart data for Chart.js.
     * 
     * GET /api/v1/dashboard/charts/fees?period=monthly&year=2026
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function feesChart(Request $request): JsonResponse
    {
        $period = $request->get('period', 'monthly');
        $year = $request->integer('year', now()->year);

        if (!in_array($period, ['monthly', 'weekly'])) {
            return $this->errorResponse('Invalid period. Use "monthly" or "weekly".');
        }

        $chartData = $this->dashboardService->getFeesChartData($period, $year);

        return $this->chartResponse($chartData['labels'], $chartData['datasets']);
    }

    /**
     * Get students distribution chart data by class.
     * 
     * GET /api/v1/dashboard/charts/students
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function studentsChart(Request $request): JsonResponse
    {
        $chartData = $this->dashboardService->getStudentsChartData();

        return $this->chartResponse($chartData['labels'], $chartData['datasets']);
    }

    /**
     * Get recent activities for the dashboard.
     * 
     * GET /api/v1/dashboard/recent-activities?limit=10
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function recentActivities(Request $request): JsonResponse
    {
        $limit = min($request->integer('limit', 10), 50);
        
        $activities = $this->dashboardService->getRecentActivities($limit);

        return $this->successResponse(
            $activities,
            'Recent activities retrieved successfully'
        );
    }

    /**
     * Clear dashboard cache (admin only).
     * 
     * POST /api/v1/dashboard/clear-cache
     * 
     * @return JsonResponse
     */
    public function clearCache(): JsonResponse
    {
        $this->dashboardService->clearCache();

        return $this->successResponse(
            null,
            'Dashboard cache cleared successfully'
        );
    }
}
