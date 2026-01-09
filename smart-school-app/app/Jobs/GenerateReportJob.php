<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\PdfReportService;
use App\Services\ReportCardService;
use App\Services\FeeReceiptService;
use App\Services\AttendanceReportService;
use App\Services\LibraryReportService;
use App\Services\TransportReportService;
use App\Services\HostelReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

/**
 * Generate Report Job
 * 
 * Prompt 441: Create Report Generation Queue Job
 * 
 * Queued job for generating PDF reports in the background.
 * Supports various report types and sends completion notifications.
 * 
 * Features:
 * - Background report generation
 * - Multiple report types support
 * - Progress tracking
 * - Completion notifications
 * - File storage management
 */
class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $reportType;
    protected array $parameters;
    protected ?int $requestedBy;
    protected ?string $callbackUrl;

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
    public $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @param string $reportType
     * @param array $parameters
     * @param int|null $requestedBy
     * @param string|null $callbackUrl
     */
    public function __construct(
        string $reportType,
        array $parameters = [],
        ?int $requestedBy = null,
        ?string $callbackUrl = null
    ) {
        $this->reportType = $reportType;
        $this->parameters = $parameters;
        $this->requestedBy = $requestedBy;
        $this->callbackUrl = $callbackUrl;
        $this->onQueue('reports');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        Log::info('Starting report generation', [
            'type' => $this->reportType,
            'parameters' => $this->parameters,
            'requested_by' => $this->requestedBy,
        ]);

        try {
            // Generate the report
            $result = $this->generateReport();

            $duration = round(microtime(true) - $startTime, 2);

            // Store report record
            $reportId = $this->storeReportRecord($result, $duration);

            Log::info('Report generation completed', [
                'type' => $this->reportType,
                'report_id' => $reportId,
                'file_path' => $result['path'] ?? null,
                'duration_seconds' => $duration,
            ]);

            // Notify user if requested
            if ($this->requestedBy) {
                $this->notifyUser($reportId, $result);
            }

        } catch (\Exception $e) {
            Log::error('Report generation failed', [
                'type' => $this->reportType,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate the report based on type.
     *
     * @return array
     */
    protected function generateReport(): array
    {
        return match ($this->reportType) {
            'report_card' => $this->generateReportCard(),
            'fee_receipt' => $this->generateFeeReceipt(),
            'attendance_daily' => $this->generateAttendanceReport('daily'),
            'attendance_monthly' => $this->generateAttendanceReport('monthly'),
            'attendance_summary' => $this->generateAttendanceReport('summary'),
            'library_inventory' => $this->generateLibraryReport('inventory'),
            'library_overdue' => $this->generateLibraryReport('overdue'),
            'transport_routes' => $this->generateTransportReport('routes'),
            'transport_vehicles' => $this->generateTransportReport('vehicles'),
            'hostel_allocation' => $this->generateHostelReport('allocation'),
            'hostel_occupancy' => $this->generateHostelReport('occupancy'),
            default => throw new \InvalidArgumentException("Unknown report type: {$this->reportType}"),
        };
    }

    /**
     * Generate report card.
     *
     * @return array
     */
    protected function generateReportCard(): array
    {
        $service = app(ReportCardService::class);
        $studentId = $this->parameters['student_id'] ?? null;
        $examId = $this->parameters['exam_id'] ?? null;

        if (!$studentId || !$examId) {
            throw new \InvalidArgumentException('student_id and exam_id are required');
        }

        $pdf = $service->generate($studentId, $examId);
        $filename = "report_cards/student_{$studentId}_exam_{$examId}_" . time() . '.pdf';
        
        Storage::disk('local')->put($filename, $pdf);

        return [
            'path' => $filename,
            'type' => 'pdf',
            'size' => strlen($pdf),
        ];
    }

    /**
     * Generate fee receipt.
     *
     * @return array
     */
    protected function generateFeeReceipt(): array
    {
        $service = app(FeeReceiptService::class);
        $transactionId = $this->parameters['transaction_id'] ?? null;

        if (!$transactionId) {
            throw new \InvalidArgumentException('transaction_id is required');
        }

        $pdf = $service->generate($transactionId);
        $filename = "receipts/transaction_{$transactionId}_" . time() . '.pdf';
        
        Storage::disk('local')->put($filename, $pdf);

        return [
            'path' => $filename,
            'type' => 'pdf',
            'size' => strlen($pdf),
        ];
    }

    /**
     * Generate attendance report.
     *
     * @param string $subType
     * @return array
     */
    protected function generateAttendanceReport(string $subType): array
    {
        $service = app(AttendanceReportService::class);
        
        $pdf = match ($subType) {
            'daily' => $service->generateDailyReport(
                $this->parameters['date'] ?? now()->format('Y-m-d'),
                $this->parameters['class_id'] ?? null,
                $this->parameters['section_id'] ?? null
            ),
            'monthly' => $service->generateMonthlyReport(
                $this->parameters['month'] ?? now()->format('Y-m'),
                $this->parameters['class_id'] ?? null,
                $this->parameters['section_id'] ?? null
            ),
            'summary' => $service->generateSummaryReport(
                $this->parameters['start_date'] ?? now()->startOfMonth()->format('Y-m-d'),
                $this->parameters['end_date'] ?? now()->format('Y-m-d'),
                $this->parameters['class_id'] ?? null
            ),
            default => throw new \InvalidArgumentException("Unknown attendance report subtype: {$subType}"),
        };

        $filename = "attendance/attendance_{$subType}_" . time() . '.pdf';
        Storage::disk('local')->put($filename, $pdf);

        return [
            'path' => $filename,
            'type' => 'pdf',
            'size' => strlen($pdf),
        ];
    }

    /**
     * Generate library report.
     *
     * @param string $subType
     * @return array
     */
    protected function generateLibraryReport(string $subType): array
    {
        $service = app(LibraryReportService::class);
        
        $pdf = match ($subType) {
            'inventory' => $service->generateInventoryReport($this->parameters['category_id'] ?? null),
            'overdue' => $service->generateOverdueReport(),
            default => throw new \InvalidArgumentException("Unknown library report subtype: {$subType}"),
        };

        $filename = "library/library_{$subType}_" . time() . '.pdf';
        Storage::disk('local')->put($filename, $pdf);

        return [
            'path' => $filename,
            'type' => 'pdf',
            'size' => strlen($pdf),
        ];
    }

    /**
     * Generate transport report.
     *
     * @param string $subType
     * @return array
     */
    protected function generateTransportReport(string $subType): array
    {
        $service = app(TransportReportService::class);
        
        $pdf = match ($subType) {
            'routes' => $service->generateRouteReport($this->parameters['route_id'] ?? null),
            'vehicles' => $service->generateVehicleReport($this->parameters['vehicle_id'] ?? null),
            default => throw new \InvalidArgumentException("Unknown transport report subtype: {$subType}"),
        };

        $filename = "transport/transport_{$subType}_" . time() . '.pdf';
        Storage::disk('local')->put($filename, $pdf);

        return [
            'path' => $filename,
            'type' => 'pdf',
            'size' => strlen($pdf),
        ];
    }

    /**
     * Generate hostel report.
     *
     * @param string $subType
     * @return array
     */
    protected function generateHostelReport(string $subType): array
    {
        $service = app(HostelReportService::class);
        
        $pdf = match ($subType) {
            'allocation' => $service->generateAllocationReport($this->parameters['hostel_id'] ?? null),
            'occupancy' => $service->generateOccupancyReport($this->parameters['hostel_id'] ?? null),
            default => throw new \InvalidArgumentException("Unknown hostel report subtype: {$subType}"),
        };

        $filename = "hostel/hostel_{$subType}_" . time() . '.pdf';
        Storage::disk('local')->put($filename, $pdf);

        return [
            'path' => $filename,
            'type' => 'pdf',
            'size' => strlen($pdf),
        ];
    }

    /**
     * Store report record in database.
     *
     * @param array $result
     * @param float $duration
     * @return int
     */
    protected function storeReportRecord(array $result, float $duration): int
    {
        return DB::table('generated_reports')->insertGetId([
            'type' => $this->reportType,
            'parameters' => json_encode($this->parameters),
            'file_path' => $result['path'],
            'file_type' => $result['type'],
            'file_size' => $result['size'],
            'generated_by' => $this->requestedBy,
            'generation_time' => $duration,
            'created_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * Notify user about report completion.
     *
     * @param int $reportId
     * @param array $result
     * @return void
     */
    protected function notifyUser(int $reportId, array $result): void
    {
        $user = User::find($this->requestedBy);
        
        if ($user) {
            DB::table('notifications')->insert([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => 'App\\Notifications\\ReportReadyNotification',
                'notifiable_type' => get_class($user),
                'notifiable_id' => $user->id,
                'data' => json_encode([
                    'type' => 'report_ready',
                    'title' => 'Report Ready',
                    'message' => "Your {$this->reportType} report is ready for download.",
                    'report_id' => $reportId,
                    'file_path' => $result['path'],
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
        Log::error('Report generation job permanently failed', [
            'type' => $this->reportType,
            'error' => $exception->getMessage(),
            'requested_by' => $this->requestedBy,
        ]);

        // Notify user about failure
        if ($this->requestedBy) {
            $user = User::find($this->requestedBy);
            if ($user) {
                DB::table('notifications')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'App\\Notifications\\ReportFailedNotification',
                    'notifiable_type' => get_class($user),
                    'notifiable_id' => $user->id,
                    'data' => json_encode([
                        'type' => 'report_failed',
                        'title' => 'Report Generation Failed',
                        'message' => "Failed to generate {$this->reportType} report. Please try again.",
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
        return ['report', $this->reportType, 'user:' . ($this->requestedBy ?? 'system')];
    }
}
