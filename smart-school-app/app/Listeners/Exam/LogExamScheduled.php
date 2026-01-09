<?php

namespace App\Listeners\Exam;

use App\Events\ExamScheduled;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log Exam Scheduled Listener
 * 
 * Prompt 470: Create Exam Event Listeners
 * 
 * Creates an audit log entry when an exam is scheduled.
 * Records exam details and affected classes/sections.
 */
class LogExamScheduled implements ShouldQueue
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
    public function handle(ExamScheduled $event): void
    {
        try {
            $exam = $event->getExam();

            $this->auditLogService->log(
                'exam_scheduled',
                'Exam scheduled',
                [
                    'exam_id' => $exam->id,
                    'exam_name' => $exam->name,
                    'exam_type_id' => $exam->exam_type_id,
                    'start_date' => $exam->start_date?->format('Y-m-d'),
                    'end_date' => $exam->end_date?->format('Y-m-d'),
                    'class_ids' => $event->classIds,
                    'section_ids' => $event->sectionIds,
                    'schedule_count' => count($event->schedules),
                ],
                $event->scheduledBy['id'] ?? null,
                'exams',
                $exam->id
            );

            Log::info('Exam scheduling logged', [
                'exam_id' => $exam->id,
                'exam_name' => $exam->name,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log exam scheduling', [
                'exam_id' => $event->exam->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(ExamScheduled $event, \Throwable $exception): void
    {
        Log::error('Exam scheduling logging job failed', [
            'exam_id' => $event->exam->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
