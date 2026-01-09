<?php

namespace App\Listeners\Attendance;

use App\Events\AttendanceMarked;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log Attendance Marked Listener
 * 
 * Prompt 469: Create Attendance Event Listeners
 * 
 * Creates an audit log entry when attendance is marked for a class/section.
 * Records who marked the attendance and the summary statistics.
 */
class LogAttendanceMarked implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The audit log service instance.
     */
    protected AuditLogService $auditLogService;

    /**
     * Create the event listener.
     */
    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Handle the event.
     */
    public function handle(AttendanceMarked $event): void
    {
        try {
            $this->auditLogService->log(
                'attendance_marked',
                'Attendance marked for class/section',
                [
                    'class_id' => $event->classId,
                    'section_id' => $event->sectionId,
                    'date' => $event->date,
                    'academic_session_id' => $event->academicSessionId,
                    'summary' => $event->summary,
                    'total_students' => count($event->attendanceRecords),
                    'present_count' => count($event->getPresentStudents()),
                    'absent_count' => count($event->getAbsentStudents()),
                    'late_count' => count($event->getLateStudents()),
                    'attendance_percentage' => $event->getAttendancePercentage(),
                ],
                $event->markedBy['id'] ?? null,
                'attendances',
                null
            );

            Log::info('Attendance marking logged', [
                'class_id' => $event->classId,
                'section_id' => $event->sectionId,
                'date' => $event->date,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log attendance marking', [
                'class_id' => $event->classId,
                'section_id' => $event->sectionId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(AttendanceMarked $event, \Throwable $exception): void
    {
        Log::error('Attendance logging job failed', [
            'class_id' => $event->classId,
            'section_id' => $event->sectionId,
            'error' => $exception->getMessage(),
        ]);
    }
}
