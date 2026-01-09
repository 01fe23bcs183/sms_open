<?php

namespace App\Listeners\Hostel;

use App\Events\HostelAssigned;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log Hostel Assignment Listener
 * 
 * Creates audit log entry for hostel room assignments.
 */
class LogHostelAssignment implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function handle(HostelAssigned $event): void
    {
        try {
            $this->auditLogService->log(
                'hostel_assignment',
                'Student assigned to hostel room',
                [
                    'student_id' => $event->student->id,
                    'student_name' => $event->student->full_name,
                    'hostel_id' => $event->hostelId,
                    'room_id' => $event->roomId,
                    'assignment_details' => $event->assignmentDetails,
                ],
                $event->assignedBy['id'] ?? null,
                'hostel_assignments',
                null
            );

            Log::info('Hostel assignment logged', [
                'student_id' => $event->student->id,
                'hostel_id' => $event->hostelId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log hostel assignment', [
                'student_id' => $event->student->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(HostelAssigned $event, \Throwable $exception): void
    {
        Log::error('Hostel assignment logging job failed', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
