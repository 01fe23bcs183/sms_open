<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Attendance;
use App\Models\FeesTransaction;
use App\Models\AcademicSession;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Dashboard Service
 * 
 * Prompt 303: Provide Dashboard Metrics and Chart Data Endpoints
 * 
 * Handles aggregations and data processing for dashboard metrics and charts.
 * Uses caching to reduce database load.
 */
class DashboardService
{
    /**
     * Cache TTL in seconds (5 minutes)
     */
    private const CACHE_TTL = 300;

    /**
     * Get aggregated dashboard metrics.
     * 
     * @return array
     */
    public function getMetrics(): array
    {
        return Cache::remember('dashboard_metrics', self::CACHE_TTL, function () {
            $activeSession = $this->getActiveSession();
            $sessionId = $activeSession?->id;

            return [
                'total_students' => $this->getTotalStudents($sessionId),
                'total_teachers' => $this->getTotalTeachers(),
                'total_classes' => $this->getTotalClasses(),
                'total_sections' => $this->getTotalSections(),
                'fees_collected' => $this->getFeesCollected($sessionId),
                'fees_pending' => $this->getFeesPending($sessionId),
                'attendance_today' => $this->getAttendanceToday($sessionId),
                'attendance_percentage' => $this->getAttendancePercentage($sessionId),
                'active_session' => $activeSession ? [
                    'id' => $activeSession->id,
                    'name' => $activeSession->name,
                    'start_date' => $activeSession->start_date?->format('Y-m-d'),
                    'end_date' => $activeSession->end_date?->format('Y-m-d'),
                ] : null,
            ];
        });
    }

    /**
     * Get fee collection chart data for Chart.js.
     * 
     * @param string $period 'monthly' or 'weekly'
     * @param int|null $year
     * @return array
     */
    public function getFeesChartData(string $period = 'monthly', ?int $year = null): array
    {
        $year = $year ?? now()->year;
        $cacheKey = "fees_chart_{$period}_{$year}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($period, $year) {
            if ($period === 'weekly') {
                return $this->getWeeklyFeesData();
            }

            return $this->getMonthlyFeesData($year);
        });
    }

    /**
     * Get attendance chart data for Chart.js.
     * 
     * @param string $period 'monthly' or 'weekly'
     * @param int|null $year
     * @return array
     */
    public function getAttendanceChartData(string $period = 'monthly', ?int $year = null): array
    {
        $year = $year ?? now()->year;
        $cacheKey = "attendance_chart_{$period}_{$year}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($period, $year) {
            if ($period === 'weekly') {
                return $this->getWeeklyAttendanceData();
            }

            return $this->getMonthlyAttendanceData($year);
        });
    }

    /**
     * Get students distribution chart data by class.
     * 
     * @return array
     */
    public function getStudentsChartData(): array
    {
        return Cache::remember('students_chart', self::CACHE_TTL, function () {
            $data = DB::table('students')
                ->join('school_classes', 'students.class_id', '=', 'school_classes.id')
                ->select('school_classes.name as class_name', DB::raw('COUNT(*) as count'))
                ->whereNull('students.deleted_at')
                ->groupBy('school_classes.id', 'school_classes.name')
                ->orderBy('school_classes.name')
                ->get();

            $labels = $data->pluck('class_name')->toArray();
            $values = $data->pluck('count')->toArray();

            $colors = $this->generateColors(count($labels));

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Students per Class',
                        'data' => $values,
                        'backgroundColor' => $colors,
                        'borderColor' => $colors,
                        'borderWidth' => 1,
                    ],
                ],
            ];
        });
    }

    /**
     * Get recent activities for the dashboard.
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentActivities(int $limit = 10): array
    {
        $activities = [];

        $recentTransactions = DB::table('fees_transactions')
            ->join('students', 'fees_transactions.student_id', '=', 'students.id')
            ->select(
                'fees_transactions.id',
                'fees_transactions.amount',
                'fees_transactions.created_at',
                'students.first_name',
                'students.last_name'
            )
            ->whereNull('fees_transactions.deleted_at')
            ->orderByDesc('fees_transactions.created_at')
            ->limit(5)
            ->get();

        foreach ($recentTransactions as $transaction) {
            $activities[] = [
                'type' => 'fee_payment',
                'icon' => 'bi-currency-dollar',
                'color' => 'success',
                'title' => 'Fee Payment Received',
                'description' => "{$transaction->first_name} {$transaction->last_name} paid " . number_format($transaction->amount, 2),
                'timestamp' => Carbon::parse($transaction->created_at)->toIso8601String(),
                'time_ago' => Carbon::parse($transaction->created_at)->diffForHumans(),
            ];
        }

        $recentStudents = DB::table('students')
            ->select('id', 'first_name', 'last_name', 'created_at')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        foreach ($recentStudents as $student) {
            $activities[] = [
                'type' => 'new_student',
                'icon' => 'bi-person-plus',
                'color' => 'primary',
                'title' => 'New Student Enrolled',
                'description' => "{$student->first_name} {$student->last_name} was enrolled",
                'timestamp' => Carbon::parse($student->created_at)->toIso8601String(),
                'time_ago' => Carbon::parse($student->created_at)->diffForHumans(),
            ];
        }

        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return array_slice($activities, 0, $limit);
    }

    /**
     * Clear dashboard cache.
     * 
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget('dashboard_metrics');
        Cache::forget('students_chart');
        
        $year = now()->year;
        Cache::forget("fees_chart_monthly_{$year}");
        Cache::forget("fees_chart_weekly_{$year}");
        Cache::forget("attendance_chart_monthly_{$year}");
        Cache::forget("attendance_chart_weekly_{$year}");
    }

    private function getActiveSession(): ?AcademicSession
    {
        return AcademicSession::where('is_active', true)->first();
    }

    private function getTotalStudents(?int $sessionId): int
    {
        $query = Student::query();
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }

        return $query->count();
    }

    private function getTotalTeachers(): int
    {
        return User::role('teacher')->count();
    }

    private function getTotalClasses(): int
    {
        return SchoolClass::count();
    }

    private function getTotalSections(): int
    {
        return Section::count();
    }

    private function getFeesCollected(?int $sessionId): float
    {
        $query = FeesTransaction::where('payment_status', 'completed');
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }

        return (float) $query->sum('amount');
    }

    private function getFeesPending(?int $sessionId): float
    {
        $query = FeesTransaction::where('payment_status', 'pending');
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }

        return (float) $query->sum('amount');
    }

    private function getAttendanceToday(?int $sessionId): array
    {
        $today = now()->format('Y-m-d');
        
        $query = Attendance::whereDate('date', $today);
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }

        $total = $query->count();
        $present = (clone $query)->where('status', 'present')->count();
        $absent = (clone $query)->where('status', 'absent')->count();
        $late = (clone $query)->where('status', 'late')->count();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
        ];
    }

    private function getAttendancePercentage(?int $sessionId): float
    {
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $today = now()->format('Y-m-d');
        
        $query = Attendance::whereBetween('date', [$startOfMonth, $today]);
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }

        $total = $query->count();
        
        if ($total === 0) {
            return 0.0;
        }

        $present = (clone $query)->where('status', 'present')->count();

        return round(($present / $total) * 100, 2);
    }

    private function getMonthlyFeesData(int $year): array
    {
        $months = [];
        $collected = [];
        $pending = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[] = Carbon::create($year, $month, 1)->format('M');

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $collected[] = (float) FeesTransaction::where('payment_status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount');

            $pending[] = (float) FeesTransaction::where('payment_status', 'pending')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount');
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Collected',
                    'data' => $collected,
                    'backgroundColor' => 'rgba(79, 70, 229, 0.8)',
                    'borderColor' => 'rgb(79, 70, 229)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Pending',
                    'data' => $pending,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                ],
            ],
        ];
    }

    private function getWeeklyFeesData(): array
    {
        $labels = [];
        $collected = [];
        $pending = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('D');

            $collected[] = (float) FeesTransaction::where('payment_status', 'completed')
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->sum('amount');

            $pending[] = (float) FeesTransaction::where('payment_status', 'pending')
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->sum('amount');
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Collected',
                    'data' => $collected,
                    'backgroundColor' => 'rgba(79, 70, 229, 0.8)',
                    'borderColor' => 'rgb(79, 70, 229)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Pending',
                    'data' => $pending,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                ],
            ],
        ];
    }

    private function getMonthlyAttendanceData(int $year): array
    {
        $months = [];
        $present = [];
        $absent = [];
        $late = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[] = Carbon::create($year, $month, 1)->format('M');

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $present[] = Attendance::where('status', 'present')
                ->whereBetween('date', [$startDate, $endDate])
                ->count();

            $absent[] = Attendance::where('status', 'absent')
                ->whereBetween('date', [$startDate, $endDate])
                ->count();

            $late[] = Attendance::where('status', 'late')
                ->whereBetween('date', [$startDate, $endDate])
                ->count();
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Present',
                    'data' => $present,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Absent',
                    'data' => $absent,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Late',
                    'data' => $late,
                    'backgroundColor' => 'rgba(234, 179, 8, 0.8)',
                    'borderColor' => 'rgb(234, 179, 8)',
                    'borderWidth' => 2,
                ],
            ],
        ];
    }

    private function getWeeklyAttendanceData(): array
    {
        $labels = [];
        $present = [];
        $absent = [];
        $late = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('D');

            $present[] = Attendance::where('status', 'present')
                ->whereDate('date', $date->format('Y-m-d'))
                ->count();

            $absent[] = Attendance::where('status', 'absent')
                ->whereDate('date', $date->format('Y-m-d'))
                ->count();

            $late[] = Attendance::where('status', 'late')
                ->whereDate('date', $date->format('Y-m-d'))
                ->count();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Present',
                    'data' => $present,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Absent',
                    'data' => $absent,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Late',
                    'data' => $late,
                    'backgroundColor' => 'rgba(234, 179, 8, 0.8)',
                    'borderColor' => 'rgb(234, 179, 8)',
                    'borderWidth' => 2,
                ],
            ],
        ];
    }

    private function generateColors(int $count): array
    {
        $baseColors = [
            'rgba(79, 70, 229, 0.8)',
            'rgba(34, 197, 94, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(234, 179, 8, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(168, 85, 247, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(20, 184, 166, 0.8)',
            'rgba(249, 115, 22, 0.8)',
            'rgba(107, 114, 128, 0.8)',
        ];

        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $baseColors[$i % count($baseColors)];
        }

        return $colors;
    }
}
