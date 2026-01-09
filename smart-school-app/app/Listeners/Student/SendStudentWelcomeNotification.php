<?php

namespace App\Listeners\Student;

use App\Events\StudentCreated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send Student Welcome Notification Listener
 * 
 * Prompt 468: Create Student Event Listeners
 * 
 * Sends welcome notification to newly admitted students and their parents.
 * Includes admission details, login credentials, and important information.
 */
class SendStudentWelcomeNotification implements ShouldQueue
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
    public function handle(StudentCreated $event): void
    {
        try {
            $student = $event->getStudent();
            $parentContacts = $event->getParentContacts();

            $notificationData = [
                'type' => 'student_welcome',
                'title' => 'Welcome to Smart School',
                'message' => $this->buildWelcomeMessage($student),
                'data' => [
                    'student_id' => $student->id,
                    'admission_number' => $student->admission_number,
                    'class' => $student->schoolClass->name ?? null,
                    'section' => $student->section->name ?? null,
                ],
            ];

            if ($student->user && $student->user->email) {
                $this->notificationService->send(
                    $student->user->id,
                    'student_welcome',
                    $notificationData,
                    ['email', 'database']
                );
            }

            foreach ($parentContacts as $type => $contact) {
                if (!empty($contact['email']) || !empty($contact['phone'])) {
                    $this->sendParentNotification($contact, $student, $type);
                }
            }

            Log::info('Student welcome notification sent', [
                'student_id' => $student->id,
                'admission_number' => $student->admission_number,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send student welcome notification', [
                'student_id' => $event->student->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Build the welcome message for the student.
     */
    protected function buildWelcomeMessage($student): string
    {
        $className = $student->schoolClass->name ?? 'N/A';
        $sectionName = $student->section->name ?? 'N/A';

        return "Welcome to Smart School! Your admission has been confirmed. " .
               "Admission Number: {$student->admission_number}. " .
               "Class: {$className}, Section: {$sectionName}. " .
               "Please login to access your student portal.";
    }

    /**
     * Send notification to parent/guardian.
     */
    protected function sendParentNotification(array $contact, $student, string $type): void
    {
        $channels = [];
        if (!empty($contact['email'])) {
            $channels[] = 'email';
        }
        if (!empty($contact['phone'])) {
            $channels[] = 'sms';
        }

        if (empty($channels)) {
            return;
        }

        $notificationData = [
            'type' => 'parent_student_admission',
            'title' => 'Student Admission Confirmation',
            'message' => "Dear {$contact['name']}, your ward has been admitted to Smart School. " .
                        "Admission Number: {$student->admission_number}.",
            'recipient_email' => $contact['email'],
            'recipient_phone' => $contact['phone'],
            'data' => [
                'student_id' => $student->id,
                'parent_type' => $type,
            ],
        ];

        $this->notificationService->sendToExternal($notificationData, $channels);
    }

    /**
     * Handle a job failure.
     */
    public function failed(StudentCreated $event, \Throwable $exception): void
    {
        Log::error('Student welcome notification job failed', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
