<?php

namespace App\Listeners\Exam;

use App\Events\ExamResultPublished;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send Exam Result Notification Listener
 * 
 * Prompt 470: Create Exam Event Listeners
 * 
 * Sends notifications to students and parents when exam results are published.
 * Includes result summary and link to view detailed results.
 */
class SendExamResultNotification implements ShouldQueue
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
    public function handle(ExamResultPublished $event): void
    {
        try {
            $exam = $event->getExam();

            $notificationData = [
                'type' => 'exam_result_published',
                'title' => 'Exam Results Published',
                'message' => "Results for '{$exam->name}' have been published. " .
                            "Login to view your detailed results and report card.",
                'data' => [
                    'exam_id' => $exam->id,
                    'exam_name' => $exam->name,
                    'class_id' => $event->classId,
                    'section_id' => $event->sectionId,
                    'pass_percentage' => $event->getPassPercentage(),
                ],
            ];

            $this->notificationService->sendToRole(
                'student',
                'exam_result_published',
                $notificationData,
                ['database', 'email', 'sms'],
                ['class_id' => $event->classId, 'section_id' => $event->sectionId]
            );

            $this->notificationService->sendToRole(
                'parent',
                'exam_result_published',
                $notificationData,
                ['database', 'email', 'sms'],
                ['class_id' => $event->classId, 'section_id' => $event->sectionId]
            );

            Log::info('Exam result notifications sent', [
                'exam_id' => $exam->id,
                'class_id' => $event->classId,
                'section_id' => $event->sectionId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send exam result notifications', [
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
        Log::error('Exam result notification job failed', [
            'exam_id' => $event->exam->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
