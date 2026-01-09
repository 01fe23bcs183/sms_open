<?php

namespace App\Listeners\Exam;

use App\Events\ExamResultPublished;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log Exam Result Published Listener
 * 
 * Prompt 470: Create Exam Event Listeners
 * 
 * Creates an audit log entry when exam results are published.
 * Records result summary and publishing details.
 */
class LogExamResultPublished implements ShouldQueue
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
    public function handle(ExamResultPublished $event): void
    {
        try {
            $exam = $event->getExam();

            $this->auditLogService->log(
                'exam_result_published',
                'Exam results published',
                [
                    'exam_id' => $exam->id,
                    'exam_name' => $exam->name,
                    'class_id' => $event->classId,
                    'section_id' => $event->sectionId,
                    'total_students' => $event->getTotalStudents(),
                    'passed_students' => $event->getPassedStudents(),
                    'failed_students' => $event->getFailedStudents(),
                    'pass_percentage' => $event->getPassPercentage(),
                    'class_average' => $event->getClassAverage(),
                ],
                $event->publishedBy['id'] ?? null,
                'exams',
                $exam->id
            );

            Log::info('Exam result publishing logged', [
                'exam_id' => $exam->id,
                'class_id' => $event->classId,
                'section_id' => $event->sectionId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log exam result publishing', [
                'exam_id' => $event->exam->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(ExamResultPublished $event, \Throwable $exception): void
    {
        Log::error('Exam result publishing logging job failed', [
            'exam_id' => $event->exam->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
