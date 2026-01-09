<?php

namespace App\Listeners\Teacher;

use App\Events\TeacherAssigned;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log Teacher Assignment Listener
 * 
 * Creates audit log entry for teacher assignments.
 */
class LogTeacherAssignment implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function handle(TeacherAssigned $event): void
    {
        try {
            $this->auditLogService->log(
                'teacher_assignment',
                'Teacher assigned to class/section/subject',
                [
                    'teacher_id' => $event->teacher->id,
                    'teacher_name' => $event->teacher->full_name,
                    'class_id' => $event->classId,
                    'section_id' => $event->sectionId,
                    'subject_id' => $event->subjectId,
                    'assignment_type' => $event->assignmentType,
                    'academic_session_id' => $event->academicSessionId,
                ],
                $event->assignedBy['id'] ?? null,
                'class_subjects',
                null
            );

            Log::info('Teacher assignment logged', [
                'teacher_id' => $event->teacher->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log teacher assignment', [
                'teacher_id' => $event->teacher->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(TeacherAssigned $event, \Throwable $exception): void
    {
        Log::error('Teacher assignment logging job failed', [
            'teacher_id' => $event->teacher->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
