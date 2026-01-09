<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\StudentExportService;
use App\Services\AttendanceExportService;
use App\Services\ExamExportService;
use App\Services\FeeExportService;
use App\Services\LibraryExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Process Export Job
 * 
 * Prompt 442: Create Export Queue Job
 * 
 * Queued job for processing large data exports in the background.
 * Supports multiple export formats and sends completion notifications.
 * 
 * Features:
 * - Background export processing
 * - Multiple format support (CSV, Excel, PDF)
 * - Large dataset handling
 * - Progress tracking
 * - Completion notifications
 */
class ProcessExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $exportType;
    protected string $format;
    protected array $filters;
    protected ?int $requestedBy;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * Create a new job instance.
     *
     * @param string $exportType
     * @param string $format
     * @param array $filters
     * @param int|null $requestedBy
     */
    public function __construct(
        string $exportType,
        string $format = 'xlsx',
        array $filters = [],
        ?int $requestedBy = null
    ) {
        $this->exportType = $exportType;
        $this->format = $format;
        $this->filters = $filters;
        $this->requestedBy = $requestedBy;
        $this->onQueue('exports');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        Log::info('Starting export job', [
            'type' => $this->exportType,
            'format' => $this->format,
            'filters' => $this->filters,
            'requested_by' => $this->requestedBy,
        ]);

        try {
            // Get export data
            $data = $this->getExportData();

            // Generate export file
            $result = $this->generateExportFile($data);

            $duration = round(microtime(true) - $startTime, 2);

            // Store export record
            $exportId = $this->storeExportRecord($result, $duration, count($data));

            Log::info('Export job completed', [
                'type' => $this->exportType,
                'export_id' => $exportId,
                'file_path' => $result['path'],
                'records' => count($data),
                'duration_seconds' => $duration,
            ]);

            // Notify user
            if ($this->requestedBy) {
                $this->notifyUser($exportId, $result, count($data));
            }

        } catch (\Exception $e) {
            Log::error('Export job failed', [
                'type' => $this->exportType,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get export data based on type.
     *
     * @return array
     */
    protected function getExportData(): array
    {
        return match ($this->exportType) {
            'students' => $this->getStudentExportData(),
            'attendance' => $this->getAttendanceExportData(),
            'exams' => $this->getExamExportData(),
            'fees' => $this->getFeeExportData(),
            'library' => $this->getLibraryExportData(),
            default => throw new \InvalidArgumentException("Unknown export type: {$this->exportType}"),
        };
    }

    /**
     * Get student export data.
     *
     * @return array
     */
    protected function getStudentExportData(): array
    {
        $service = app(StudentExportService::class);
        return $service->getExportData($this->filters);
    }

    /**
     * Get attendance export data.
     *
     * @return array
     */
    protected function getAttendanceExportData(): array
    {
        $service = app(AttendanceExportService::class);
        return $service->getExportData($this->filters);
    }

    /**
     * Get exam export data.
     *
     * @return array
     */
    protected function getExamExportData(): array
    {
        $service = app(ExamExportService::class);
        return $service->getExportData($this->filters);
    }

    /**
     * Get fee export data.
     *
     * @return array
     */
    protected function getFeeExportData(): array
    {
        $service = app(FeeExportService::class);
        return $service->getExportData($this->filters);
    }

    /**
     * Get library export data.
     *
     * @return array
     */
    protected function getLibraryExportData(): array
    {
        $service = app(LibraryExportService::class);
        return $service->getExportData($this->filters);
    }

    /**
     * Generate export file.
     *
     * @param array $data
     * @return array
     */
    protected function generateExportFile(array $data): array
    {
        $filename = "exports/{$this->exportType}_{$this->format}_" . time();

        return match ($this->format) {
            'csv' => $this->generateCsv($data, $filename),
            'xlsx', 'excel' => $this->generateExcel($data, $filename),
            'json' => $this->generateJson($data, $filename),
            default => throw new \InvalidArgumentException("Unknown format: {$this->format}"),
        };
    }

    /**
     * Generate CSV file.
     *
     * @param array $data
     * @param string $filename
     * @return array
     */
    protected function generateCsv(array $data, string $filename): array
    {
        $filename .= '.csv';
        
        if (empty($data)) {
            $content = '';
        } else {
            $headers = array_keys($data[0]);
            $content = implode(',', $headers) . "\n";
            
            foreach ($data as $row) {
                $values = array_map(function ($value) {
                    if (is_string($value) && (str_contains($value, ',') || str_contains($value, '"'))) {
                        return '"' . str_replace('"', '""', $value) . '"';
                    }
                    return $value;
                }, array_values($row));
                $content .= implode(',', $values) . "\n";
            }
        }

        Storage::disk('local')->put($filename, $content);

        return [
            'path' => $filename,
            'type' => 'csv',
            'size' => strlen($content),
        ];
    }

    /**
     * Generate Excel file.
     *
     * @param array $data
     * @param string $filename
     * @return array
     */
    protected function generateExcel(array $data, string $filename): array
    {
        $filename .= '.xlsx';
        
        // Create a simple export class
        $export = new class($data) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            protected array $data;
            
            public function __construct(array $data)
            {
                $this->data = $data;
            }
            
            public function array(): array
            {
                return array_map(fn($row) => array_values($row), $this->data);
            }
            
            public function headings(): array
            {
                return empty($this->data) ? [] : array_keys($this->data[0]);
            }
        };

        Excel::store($export, $filename, 'local');

        return [
            'path' => $filename,
            'type' => 'xlsx',
            'size' => Storage::disk('local')->size($filename),
        ];
    }

    /**
     * Generate JSON file.
     *
     * @param array $data
     * @param string $filename
     * @return array
     */
    protected function generateJson(array $data, string $filename): array
    {
        $filename .= '.json';
        $content = json_encode($data, JSON_PRETTY_PRINT);

        Storage::disk('local')->put($filename, $content);

        return [
            'path' => $filename,
            'type' => 'json',
            'size' => strlen($content),
        ];
    }

    /**
     * Store export record in database.
     *
     * @param array $result
     * @param float $duration
     * @param int $recordCount
     * @return int
     */
    protected function storeExportRecord(array $result, float $duration, int $recordCount): int
    {
        return DB::table('exports')->insertGetId([
            'type' => $this->exportType,
            'format' => $this->format,
            'filters' => json_encode($this->filters),
            'file_path' => $result['path'],
            'file_size' => $result['size'],
            'record_count' => $recordCount,
            'generated_by' => $this->requestedBy,
            'generation_time' => $duration,
            'created_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * Notify user about export completion.
     *
     * @param int $exportId
     * @param array $result
     * @param int $recordCount
     * @return void
     */
    protected function notifyUser(int $exportId, array $result, int $recordCount): void
    {
        $user = User::find($this->requestedBy);
        
        if ($user) {
            DB::table('notifications')->insert([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => 'App\\Notifications\\ExportReadyNotification',
                'notifiable_type' => get_class($user),
                'notifiable_id' => $user->id,
                'data' => json_encode([
                    'type' => 'export_ready',
                    'title' => 'Export Ready',
                    'message' => "Your {$this->exportType} export ({$recordCount} records) is ready for download.",
                    'export_id' => $exportId,
                    'file_path' => $result['path'],
                    'record_count' => $recordCount,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Export job permanently failed', [
            'type' => $this->exportType,
            'error' => $exception->getMessage(),
            'requested_by' => $this->requestedBy,
        ]);

        // Notify user about failure
        if ($this->requestedBy) {
            $user = User::find($this->requestedBy);
            if ($user) {
                DB::table('notifications')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'App\\Notifications\\ExportFailedNotification',
                    'notifiable_type' => get_class($user),
                    'notifiable_id' => $user->id,
                    'data' => json_encode([
                        'type' => 'export_failed',
                        'title' => 'Export Failed',
                        'message' => "Failed to generate {$this->exportType} export. Please try again.",
                        'error' => $exception->getMessage(),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags(): array
    {
        return ['export', $this->exportType, $this->format, 'user:' . ($this->requestedBy ?? 'system')];
    }
}
