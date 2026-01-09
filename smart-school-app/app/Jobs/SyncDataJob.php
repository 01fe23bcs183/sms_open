<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Sync Data Job
 * 
 * Prompt 444: Create Data Sync Queue Job
 * 
 * Queued job for synchronizing data between different parts of the system
 * or with external services. Handles cache updates, statistics recalculation,
 * and data consistency checks.
 * 
 * Features:
 * - Dashboard statistics sync
 * - Attendance statistics sync
 * - Fee statistics sync
 * - Cache warming
 * - Data consistency checks
 */
class SyncDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $syncType;
    protected array $parameters;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @param string $syncType
     * @param array $parameters
     */
    public function __construct(string $syncType, array $parameters = [])
    {
        $this->syncType = $syncType;
        $this->parameters = $parameters;
        $this->onQueue('sync');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        Log::info('Starting data sync job', [
            'type' => $this->syncType,
            'parameters' => $this->parameters,
        ]);

        try {
            $result = match ($this->syncType) {
                'dashboard' => $this->syncDashboardStatistics(),
                'attendance' => $this->syncAttendanceStatistics(),
                'fees' => $this->syncFeeStatistics(),
                'library' => $this->syncLibraryStatistics(),
                'cache_warm' => $this->warmCache(),
                'consistency_check' => $this->runConsistencyCheck(),
                'all' => $this->syncAll(),
                default => throw new \InvalidArgumentException("Unknown sync type: {$this->syncType}"),
            };

            $duration = round(microtime(true) - $startTime, 2);

            Log::info('Data sync job completed', [
                'type' => $this->syncType,
                'result' => $result,
                'duration_seconds' => $duration,
            ]);

        } catch (\Exception $e) {
            Log::error('Data sync job failed', [
                'type' => $this->syncType,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Sync dashboard statistics.
     *
     * @return array
     */
    protected function syncDashboardStatistics(): array
    {
        $stats = [
            'students' => $this->getStudentStats(),
            'teachers' => $this->getTeacherStats(),
            'attendance' => $this->getAttendanceOverview(),
            'fees' => $this->getFeeOverview(),
            'library' => $this->getLibraryOverview(),
        ];

        // Cache the statistics
        Cache::put('dashboard_statistics', $stats, now()->addHours(1));

        return ['cached' => true, 'stats_count' => count($stats)];
    }

    /**
     * Get student statistics.
     *
     * @return array
     */
    protected function getStudentStats(): array
    {
        return [
            'total' => DB::table('students')->whereNull('deleted_at')->count(),
            'active' => DB::table('students')->where('status', 'active')->whereNull('deleted_at')->count(),
            'by_class' => DB::table('students')
                ->select('class_id', DB::raw('count(*) as count'))
                ->whereNull('deleted_at')
                ->groupBy('class_id')
                ->pluck('count', 'class_id')
                ->toArray(),
        ];
    }

    /**
     * Get teacher statistics.
     *
     * @return array
     */
    protected function getTeacherStats(): array
    {
        return [
            'total' => DB::table('users')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('roles.name', 'teacher')
                ->where('users.is_active', true)
                ->count(),
        ];
    }

    /**
     * Get attendance overview.
     *
     * @return array
     */
    protected function getAttendanceOverview(): array
    {
        $today = now()->format('Y-m-d');
        
        return [
            'today_present' => DB::table('attendances')
                ->where('date', $today)
                ->where('status', 'present')
                ->count(),
            'today_absent' => DB::table('attendances')
                ->where('date', $today)
                ->where('status', 'absent')
                ->count(),
            'today_late' => DB::table('attendances')
                ->where('date', $today)
                ->where('status', 'late')
                ->count(),
        ];
    }

    /**
     * Get fee overview.
     *
     * @return array
     */
    protected function getFeeOverview(): array
    {
        return [
            'total_collected' => DB::table('fees_transactions')
                ->where('payment_status', 'completed')
                ->sum('amount_paid'),
            'pending' => DB::table('fees_transactions')
                ->where('payment_status', 'pending')
                ->sum('amount'),
            'today_collected' => DB::table('fees_transactions')
                ->where('payment_status', 'completed')
                ->whereDate('payment_date', now())
                ->sum('amount_paid'),
        ];
    }

    /**
     * Get library overview.
     *
     * @return array
     */
    protected function getLibraryOverview(): array
    {
        return [
            'total_books' => DB::table('library_books')->whereNull('deleted_at')->sum('quantity'),
            'issued' => DB::table('library_issues')
                ->whereNull('return_date')
                ->whereNull('deleted_at')
                ->count(),
            'overdue' => DB::table('library_issues')
                ->whereNull('return_date')
                ->where('due_date', '<', now())
                ->whereNull('deleted_at')
                ->count(),
        ];
    }

    /**
     * Sync attendance statistics.
     *
     * @return array
     */
    protected function syncAttendanceStatistics(): array
    {
        $month = $this->parameters['month'] ?? now()->format('Y-m');
        
        // Calculate monthly attendance statistics per student
        $stats = DB::table('attendances')
            ->select(
                'student_id',
                DB::raw('COUNT(*) as total_days'),
                DB::raw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days"),
                DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days"),
                DB::raw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days")
            )
            ->where('date', 'like', $month . '%')
            ->groupBy('student_id')
            ->get();

        // Store in cache or summary table
        foreach ($stats as $stat) {
            Cache::put(
                "attendance_stats_{$stat->student_id}_{$month}",
                [
                    'total_days' => $stat->total_days,
                    'present_days' => $stat->present_days,
                    'absent_days' => $stat->absent_days,
                    'late_days' => $stat->late_days,
                    'attendance_percentage' => $stat->total_days > 0 
                        ? round(($stat->present_days / $stat->total_days) * 100, 2) 
                        : 0,
                ],
                now()->addDays(7)
            );
        }

        return ['students_processed' => $stats->count(), 'month' => $month];
    }

    /**
     * Sync fee statistics.
     *
     * @return array
     */
    protected function syncFeeStatistics(): array
    {
        $academicSessionId = $this->parameters['academic_session_id'] ?? null;

        $query = DB::table('fees_transactions');
        
        if ($academicSessionId) {
            $query->where('academic_session_id', $academicSessionId);
        }

        $stats = [
            'total_amount' => (clone $query)->sum('amount'),
            'collected_amount' => (clone $query)->where('payment_status', 'completed')->sum('amount_paid'),
            'pending_amount' => (clone $query)->where('payment_status', 'pending')->sum('amount'),
            'transaction_count' => (clone $query)->count(),
            'completed_count' => (clone $query)->where('payment_status', 'completed')->count(),
        ];

        $cacheKey = $academicSessionId 
            ? "fee_stats_session_{$academicSessionId}" 
            : 'fee_stats_all';

        Cache::put($cacheKey, $stats, now()->addHours(1));

        return $stats;
    }

    /**
     * Sync library statistics.
     *
     * @return array
     */
    protected function syncLibraryStatistics(): array
    {
        $stats = [
            'total_books' => DB::table('library_books')->whereNull('deleted_at')->count(),
            'total_copies' => DB::table('library_books')->whereNull('deleted_at')->sum('quantity'),
            'available_copies' => DB::table('library_books')->whereNull('deleted_at')->sum('available_quantity'),
            'total_members' => DB::table('library_members')->whereNull('deleted_at')->count(),
            'active_issues' => DB::table('library_issues')
                ->whereNull('return_date')
                ->whereNull('deleted_at')
                ->count(),
            'overdue_issues' => DB::table('library_issues')
                ->whereNull('return_date')
                ->where('due_date', '<', now())
                ->whereNull('deleted_at')
                ->count(),
            'total_fines' => DB::table('library_issues')
                ->whereNull('deleted_at')
                ->sum('fine_amount'),
            'collected_fines' => DB::table('library_issues')
                ->where('fine_paid', true)
                ->whereNull('deleted_at')
                ->sum('fine_amount'),
        ];

        Cache::put('library_statistics', $stats, now()->addHours(1));

        return $stats;
    }

    /**
     * Warm cache with frequently accessed data.
     *
     * @return array
     */
    protected function warmCache(): array
    {
        $warmed = [];

        // Warm dashboard statistics
        $this->syncDashboardStatistics();
        $warmed[] = 'dashboard_statistics';

        // Warm active academic session
        $activeSession = DB::table('academic_sessions')
            ->where('is_active', true)
            ->first();
        if ($activeSession) {
            Cache::put('active_academic_session', $activeSession, now()->addHours(24));
            $warmed[] = 'active_academic_session';
        }

        // Warm classes list
        $classes = DB::table('classes')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
        Cache::put('classes_list', $classes, now()->addHours(24));
        $warmed[] = 'classes_list';

        // Warm sections list
        $sections = DB::table('sections')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
        Cache::put('sections_list', $sections, now()->addHours(24));
        $warmed[] = 'sections_list';

        return ['warmed_caches' => $warmed];
    }

    /**
     * Run data consistency checks.
     *
     * @return array
     */
    protected function runConsistencyCheck(): array
    {
        $issues = [];

        // Check for orphaned student records
        $orphanedStudents = DB::table('students')
            ->leftJoin('users', 'students.user_id', '=', 'users.id')
            ->whereNull('users.id')
            ->whereNull('students.deleted_at')
            ->count();
        if ($orphanedStudents > 0) {
            $issues[] = "Found {$orphanedStudents} students without user accounts";
        }

        // Check for library issues without books
        $orphanedIssues = DB::table('library_issues')
            ->leftJoin('library_books', 'library_issues.book_id', '=', 'library_books.id')
            ->whereNull('library_books.id')
            ->whereNull('library_issues.deleted_at')
            ->count();
        if ($orphanedIssues > 0) {
            $issues[] = "Found {$orphanedIssues} library issues without books";
        }

        // Check for fee transactions without students
        $orphanedTransactions = DB::table('fees_transactions')
            ->leftJoin('students', 'fees_transactions.student_id', '=', 'students.id')
            ->whereNull('students.id')
            ->count();
        if ($orphanedTransactions > 0) {
            $issues[] = "Found {$orphanedTransactions} fee transactions without students";
        }

        // Log issues if found
        if (!empty($issues)) {
            Log::warning('Data consistency issues found', ['issues' => $issues]);
        }

        return [
            'issues_found' => count($issues),
            'issues' => $issues,
        ];
    }

    /**
     * Sync all statistics.
     *
     * @return array
     */
    protected function syncAll(): array
    {
        $results = [];

        $results['dashboard'] = $this->syncDashboardStatistics();
        $results['attendance'] = $this->syncAttendanceStatistics();
        $results['fees'] = $this->syncFeeStatistics();
        $results['library'] = $this->syncLibraryStatistics();
        $results['cache'] = $this->warmCache();

        return $results;
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Data sync job permanently failed', [
            'type' => $this->syncType,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags(): array
    {
        return ['sync', $this->syncType];
    }
}
