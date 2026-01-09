<?php

namespace App\Listeners\Attendance;

use App\Events\AttendanceMarked;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send Attendance Notification Listener
 * 
 * Prompt 469: Create Attendance Event Listeners
 * 
 * Sends attendance notifications to parents when their child is marked absent or late.
 * Supports SMS and email notifications based on parent preferences.
 */
class SendAttendanceNotification implements ShouldQueue
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
    public function handle(AttendanceMarked $event): void
    {
        try {
            $absentStudents = $event->getAbsentStudents();
            $lateStudents = $event->getLateStudents();

            foreach ($absentStudents as $record) {
                $this->sendAbsentNotification($record, $event->date);
            }

            foreach ($lateStudents as $record) {
                $this->sendLateNotification($record, $event->date);
            }

            Log::info('Attendance notifications sent', [
                'class_id' => $event->classId,
                'section_id' => $event->sectionId,
                'date' => $event->date,
                'absent_count' => count($absentStudents),
                'late_count' => count($lateStudents),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send attendance notifications', [
                'class_id' => $event->classId,
                'section_id' => $event->sectionId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Send notification for absent student.
     */
    protected function sendAbsentNotification(array $record, string $date): void
    {
        $studentName = $record['student_name'] ?? 'Your child';
        $message = "Dear Parent, {$studentName} was marked absent on {$date}. " .
                   "Please contact the school if this is incorrect.";

        $this->sendParentNotification($record, 'absent', $message);
    }

    /**
     * Send notification for late student.
     */
    protected function sendLateNotification(array $record, string $date): void
    {
        $studentName = $record['student_name'] ?? 'Your child';
        $message = "Dear Parent, {$studentName} arrived late to school on {$date}.";

        $this->sendParentNotification($record, 'late', $message);
    }

    /**
     * Send notification to parent.
     */
    protected function sendParentNotification(array $record, string $type, string $message): void
    {
        $parentPhone = $record['parent_phone'] ?? null;
        $parentEmail = $record['parent_email'] ?? null;

        if (!$parentPhone && !$parentEmail) {
            return;
        }

        $channels = [];
        if ($parentPhone) {
            $channels[] = 'sms';
        }
        if ($parentEmail) {
            $channels[] = 'email';
        }

        $notificationData = [
            'type' => "attendance_{$type}",
            'title' => 'Attendance Alert',
            'message' => $message,
            'recipient_phone' => $parentPhone,
            'recipient_email' => $parentEmail,
            'data' => [
                'student_id' => $record['student_id'] ?? null,
                'attendance_type' => $type,
            ],
        ];

        $this->notificationService->sendToExternal($notificationData, $channels);
    }

    /**
     * Handle a job failure.
     */
    public function failed(AttendanceMarked $event, \Throwable $exception): void
    {
        Log::error('Attendance notification job failed', [
            'class_id' => $event->classId,
            'section_id' => $event->sectionId,
            'error' => $exception->getMessage(),
        ]);
    }
}
