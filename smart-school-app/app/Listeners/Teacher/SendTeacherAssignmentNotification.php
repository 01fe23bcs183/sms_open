<?php

namespace App\Listeners\Teacher;

use App\Events\TeacherAssigned;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send Teacher Assignment Notification Listener
 * 
 * Sends notification to teacher when assigned to a class/section/subject.
 */
class SendTeacherAssignmentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 60;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(TeacherAssigned $event): void
    {
        try {
            $teacher = $event->getTeacher();
            $assignmentType = $event->isClassTeacherAssignment() ? 'Class Teacher' : 'Subject Teacher';

            $message = "You have been assigned as {$assignmentType}. Please check your dashboard for details.";

            $this->notificationService->send(
                $teacher->id,
                'teacher_assignment',
                [
                    'type' => 'teacher_assignment',
                    'title' => 'New Assignment',
                    'message' => $message,
                    'data' => [
                        'class_id' => $event->classId,
                        'section_id' => $event->sectionId,
                        'subject_id' => $event->subjectId,
                        'assignment_type' => $event->assignmentType,
                    ],
                ],
                ['database', 'email']
            );

            Log::info('Teacher assignment notification sent', [
                'teacher_id' => $teacher->id,
                'assignment_type' => $event->assignmentType,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send teacher assignment notification', [
                'teacher_id' => $event->teacher->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(TeacherAssigned $event, \Throwable $exception): void
    {
        Log::error('Teacher assignment notification job failed', [
            'teacher_id' => $event->teacher->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
