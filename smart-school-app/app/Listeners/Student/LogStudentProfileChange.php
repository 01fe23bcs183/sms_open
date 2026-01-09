<?php

namespace App\Listeners\Student;

use App\Events\StudentUpdated;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log Student Profile Change Listener
 * 
 * Prompt 468: Create Student Event Listeners
 * 
 * Creates an audit log entry for student profile changes.
 * Records what fields were changed and their old/new values.
 */
class LogStudentProfileChange implements ShouldQueue
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
    public function handle(StudentUpdated $event): void
    {
        try {
            $student = $event->getStudent();
            $changedFields = $event->getChangedFields();

            if (empty($changedFields)) {
                return;
            }

            $changes = [];
            foreach ($changedFields as $field) {
                $changes[$field] = [
                    'old' => $event->getOriginalValue($field),
                    'new' => $event->getNewValue($field),
                ];
            }

            $this->auditLogService->log(
                'student_profile_update',
                'Student profile updated',
                [
                    'student_id' => $student->id,
                    'admission_number' => $student->admission_number,
                    'changed_fields' => $changedFields,
                    'changes' => $changes,
                ],
                $event->updatedBy['id'] ?? null,
                'students',
                $student->id
            );

            Log::info('Student profile change logged', [
                'student_id' => $student->id,
                'changed_fields' => $changedFields,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log student profile change', [
                'student_id' => $event->student->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(StudentUpdated $event, \Throwable $exception): void
    {
        Log::error('Student profile change logging job failed', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
