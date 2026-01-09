<?php

namespace App\Listeners\Student;

use App\Events\StudentCreated;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log Student Admission Listener
 * 
 * Prompt 468: Create Student Event Listeners
 * 
 * Creates an audit log entry for new student admissions.
 * Records admission details for compliance and tracking.
 */
class LogStudentAdmission implements ShouldQueue
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
    public function handle(StudentCreated $event): void
    {
        try {
            $student = $event->getStudent();

            $this->auditLogService->log(
                'student_admission',
                'Student admitted to school',
                [
                    'student_id' => $student->id,
                    'admission_number' => $student->admission_number,
                    'class_id' => $student->class_id,
                    'section_id' => $student->section_id,
                    'academic_session_id' => $student->academic_session_id,
                    'date_of_admission' => $student->date_of_admission?->format('Y-m-d'),
                    'admission_type' => $student->admission_type,
                    'is_rte' => $student->is_rte,
                ],
                $event->createdBy['id'] ?? null,
                'students',
                $student->id
            );

            Log::info('Student admission logged', [
                'student_id' => $student->id,
                'admission_number' => $student->admission_number,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log student admission', [
                'student_id' => $event->student->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(StudentCreated $event, \Throwable $exception): void
    {
        Log::error('Student admission logging job failed', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
