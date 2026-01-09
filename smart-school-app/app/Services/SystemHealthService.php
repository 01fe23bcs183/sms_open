<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

/**
 * System Health Service
 * 
 * Prompt 452: Create System Health Service
 * 
 * Monitors system health and provides diagnostics for the
 * Smart School Management System.
 * 
 * Features:
 * - Database connectivity check
 * - Cache connectivity check
 * - Storage availability check
 * - Queue health check
 * - System resource monitoring
 * - Performance metrics
 */
class SystemHealthService
{
    /**
     * Run all health checks.
     *
     * @return array
     */
    public function checkAll(): array
    {
        return [
            'status' => $this->getOverallStatus(),
            'timestamp' => now()->toIso8601String(),
            'checks' => [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'storage' => $this->checkStorage(),
                'queue' => $this->checkQueue(),
                'memory' => $this->checkMemory(),
                'disk' => $this->checkDisk(),
            ],
            'metrics' => $this->getMetrics(),
        ];
    }

    /**
     * Get overall system status.
     *
     * @return string
     */
    public function getOverallStatus(): string
    {
        $checks = [
            $this->checkDatabase()['status'],
            $this->checkCache()['status'],
            $this->checkStorage()['status'],
        ];

        if (in_array('error', $checks)) {
            return 'error';
        }

        if (in_array('warning', $checks)) {
            return 'warning';
        }

        return 'healthy';
    }

    /**
     * Check database connectivity.
     *
     * @return array
     */
    public function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $latency = round((microtime(true) - $start) * 1000, 2);

            // Check if we can query
            $start = microtime(true);
            DB::select('SELECT 1');
            $queryLatency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'healthy',
                'message' => 'Database connection successful',
                'connection_latency_ms' => $latency,
                'query_latency_ms' => $queryLatency,
                'driver' => config('database.default'),
            ];
        } catch (\Exception $e) {
            Log::error('Database health check failed', ['error' => $e->getMessage()]);

            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache connectivity.
     *
     * @return array
     */
    public function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            $value = 'test_value';

            $start = microtime(true);
            Cache::put($key, $value, 10);
            $writeLatency = round((microtime(true) - $start) * 1000, 2);

            $start = microtime(true);
            $retrieved = Cache::get($key);
            $readLatency = round((microtime(true) - $start) * 1000, 2);

            Cache::forget($key);

            if ($retrieved !== $value) {
                return [
                    'status' => 'error',
                    'message' => 'Cache read/write mismatch',
                ];
            }

            return [
                'status' => 'healthy',
                'message' => 'Cache is working',
                'write_latency_ms' => $writeLatency,
                'read_latency_ms' => $readLatency,
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            Log::error('Cache health check failed', ['error' => $e->getMessage()]);

            return [
                'status' => 'error',
                'message' => 'Cache check failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage availability.
     *
     * @return array
     */
    public function checkStorage(): array
    {
        $results = [];
        $disks = ['local', 'public'];

        foreach ($disks as $disk) {
            try {
                $testFile = 'health_check_' . time() . '.txt';
                
                Storage::disk($disk)->put($testFile, 'test');
                $exists = Storage::disk($disk)->exists($testFile);
                Storage::disk($disk)->delete($testFile);

                $results[$disk] = [
                    'status' => $exists ? 'healthy' : 'error',
                    'writable' => $exists,
                ];
            } catch (\Exception $e) {
                $results[$disk] = [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        $hasError = collect($results)->contains('status', 'error');

        return [
            'status' => $hasError ? 'error' : 'healthy',
            'message' => $hasError ? 'Some storage disks have issues' : 'All storage disks are working',
            'disks' => $results,
        ];
    }

    /**
     * Check queue health.
     *
     * @return array
     */
    public function checkQueue(): array
    {
        try {
            $driver = config('queue.default');

            // For database queue, check the jobs table
            if ($driver === 'database') {
                $pendingJobs = DB::table('jobs')->count();
                $failedJobs = DB::table('failed_jobs')->count();

                return [
                    'status' => $failedJobs > 100 ? 'warning' : 'healthy',
                    'message' => 'Queue is operational',
                    'driver' => $driver,
                    'pending_jobs' => $pendingJobs,
                    'failed_jobs' => $failedJobs,
                ];
            }

            // For sync queue
            if ($driver === 'sync') {
                return [
                    'status' => 'healthy',
                    'message' => 'Using sync queue driver',
                    'driver' => $driver,
                ];
            }

            // For Redis queue
            if ($driver === 'redis') {
                try {
                    $connection = Queue::connection('redis');
                    return [
                        'status' => 'healthy',
                        'message' => 'Redis queue is operational',
                        'driver' => $driver,
                    ];
                } catch (\Exception $e) {
                    return [
                        'status' => 'error',
                        'message' => 'Redis queue connection failed',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            return [
                'status' => 'healthy',
                'message' => 'Queue driver configured',
                'driver' => $driver,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue check failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check memory usage.
     *
     * @return array
     */
    public function checkMemory(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = $this->getMemoryLimit();

        $usagePercent = $memoryLimit > 0 ? round(($memoryUsage / $memoryLimit) * 100, 2) : 0;

        $status = 'healthy';
        if ($usagePercent > 90) {
            $status = 'error';
        } elseif ($usagePercent > 75) {
            $status = 'warning';
        }

        return [
            'status' => $status,
            'message' => "Memory usage: {$usagePercent}%",
            'current_usage' => $this->formatBytes($memoryUsage),
            'peak_usage' => $this->formatBytes($memoryPeak),
            'limit' => $this->formatBytes($memoryLimit),
            'usage_percent' => $usagePercent,
        ];
    }

    /**
     * Check disk space.
     *
     * @return array
     */
    public function checkDisk(): array
    {
        $path = storage_path();
        $totalSpace = disk_total_space($path);
        $freeSpace = disk_free_space($path);
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercent = round(($usedSpace / $totalSpace) * 100, 2);

        $status = 'healthy';
        if ($usagePercent > 95) {
            $status = 'error';
        } elseif ($usagePercent > 85) {
            $status = 'warning';
        }

        return [
            'status' => $status,
            'message' => "Disk usage: {$usagePercent}%",
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'usage_percent' => $usagePercent,
        ];
    }

    /**
     * Get system metrics.
     *
     * @return array
     */
    public function getMetrics(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_time' => now()->toIso8601String(),
            'timezone' => config('app.timezone'),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
            'uptime' => $this->getUptime(),
        ];
    }

    /**
     * Get database statistics.
     *
     * @return array
     */
    public function getDatabaseStats(): array
    {
        try {
            $tables = DB::select('SHOW TABLE STATUS');
            $totalSize = 0;
            $tableStats = [];

            foreach ($tables as $table) {
                $size = ($table->Data_length ?? 0) + ($table->Index_length ?? 0);
                $totalSize += $size;
                $tableStats[$table->Name] = [
                    'rows' => $table->Rows ?? 0,
                    'size' => $this->formatBytes($size),
                ];
            }

            return [
                'total_tables' => count($tables),
                'total_size' => $this->formatBytes($totalSize),
                'tables' => $tableStats,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get application statistics.
     *
     * @return array
     */
    public function getApplicationStats(): array
    {
        return [
            'users' => [
                'total' => DB::table('users')->count(),
                'active' => DB::table('users')->where('is_active', true)->count(),
            ],
            'students' => [
                'total' => DB::table('students')->whereNull('deleted_at')->count(),
            ],
            'recent_activity' => [
                'logins_today' => DB::table('audit_logs')
                    ->where('action', 'login')
                    ->whereDate('created_at', now())
                    ->count(),
            ],
        ];
    }

    /**
     * Get server uptime.
     *
     * @return string|null
     */
    protected function getUptime(): ?string
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $uptime = @file_get_contents('/proc/uptime');
            if ($uptime) {
                $seconds = (int) explode(' ', $uptime)[0];
                return $this->formatDuration($seconds);
            }
        }

        return null;
    }

    /**
     * Get PHP memory limit in bytes.
     *
     * @return int
     */
    protected function getMemoryLimit(): int
    {
        $limit = ini_get('memory_limit');
        
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($limit, -1));
        $value = (int) $limit;

        switch ($unit) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $value *= 1024 * 1024;
                break;
            case 'k':
                $value *= 1024;
                break;
        }

        return $value;
    }

    /**
     * Format bytes to human readable.
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Format duration in seconds to human readable.
     *
     * @param int $seconds
     * @return string
     */
    protected function formatDuration(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $parts = [];
        if ($days > 0) {
            $parts[] = "{$days}d";
        }
        if ($hours > 0) {
            $parts[] = "{$hours}h";
        }
        if ($minutes > 0) {
            $parts[] = "{$minutes}m";
        }

        return implode(' ', $parts) ?: '0m';
    }

    /**
     * Log health check results.
     *
     * @return void
     */
    public function logHealthCheck(): void
    {
        $results = $this->checkAll();

        Log::info('System health check', [
            'status' => $results['status'],
            'checks' => array_map(fn($check) => $check['status'], $results['checks']),
        ]);
    }

    /**
     * Get health check summary for API response.
     *
     * @return array
     */
    public function getSummary(): array
    {
        $status = $this->getOverallStatus();

        return [
            'status' => $status,
            'healthy' => $status === 'healthy',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
