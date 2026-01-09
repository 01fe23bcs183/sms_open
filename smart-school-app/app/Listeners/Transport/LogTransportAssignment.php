<?php

namespace App\Listeners\Transport;

use App\Events\TransportAssigned;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log Transport Assignment Listener
 * 
 * Creates audit log entry for transport assignments.
 */
class LogTransportAssignment implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function handle(TransportAssigned $event): void
    {
        try {
            $this->auditLogService->log(
                'transport_assignment',
                'Student assigned to transport route',
                [
                    'student_id' => $event->student->id,
                    'student_name' => $event->student->full_name,
                    'route_id' => $event->routeId,
                    'vehicle_id' => $event->vehicleId,
                    'stop_id' => $event->stopId,
                    'assignment_details' => $event->assignmentDetails,
                ],
                $event->assignedBy['id'] ?? null,
                'transport_students',
                null
            );

            Log::info('Transport assignment logged', [
                'student_id' => $event->student->id,
                'route_id' => $event->routeId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log transport assignment', [
                'student_id' => $event->student->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(TransportAssigned $event, \Throwable $exception): void
    {
        Log::error('Transport assignment logging job failed', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
