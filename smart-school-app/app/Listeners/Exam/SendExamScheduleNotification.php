<?php

namespace App\Listeners\Exam;

use App\Events\ExamScheduled;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send Exam Schedule Notification Listener
 * 
 * Prompt 470: Create Exam Event Listeners
 * 
 * Sends notifications to students and teachers when an exam is scheduled.
 * Includes exam details, dates, and schedule information.
 */
class SendExamScheduleNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 60;

    /**
     * The notification service instance.
     */
    protected NotificationService $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(ExamScheduled $event): void
    {
        try {
            $exam = $event->getExam();
            $dateRange = $event->getDateRange();

            $notificationData = [
                'type' => 'exam_scheduled',
                'title' => 'New Exam Scheduled',
                'message' => "Exam '{$exam->name}' has been scheduled for {$dateRange}. " .
                            "Please check the exam schedule for details.",
                'data' => [
                    'exam_id' => $exam->id,
                    'exam_name' => $exam->name,
                    'date_range' => $dateRange,
                    'schedule_count' => count($event->schedules),
                ],
            ];

            foreach ($event->classIds as $classId) {
                $this->notificationService->sendToRole(
                    'student',
                    'exam_scheduled',
                    $notificationData,
                    ['database', 'email'],
                    ['class_id' => $classId]
                );
            }

            $this->notificationService->sendToRole(
                'teacher',
                'exam_scheduled',
                $notificationData,
                ['database', 'email']
            );

            Log::info('Exam schedule notifications sent', [
                'exam_id' => $exam->id,
                'exam_name' => $exam->name,
                'class_ids' => $event->classIds,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send exam schedule notifications', [
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
        Log::error('Exam schedule notification job failed', [
            'exam_id' => $event->exam->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
