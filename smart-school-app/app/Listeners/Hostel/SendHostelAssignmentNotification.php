<?php

namespace App\Listeners\Hostel;

use App\Events\HostelAssigned;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send Hostel Assignment Notification Listener
 * 
 * Sends notification to student/parents when hostel room is assigned.
 */
class SendHostelAssignmentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 60;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(HostelAssigned $event): void
    {
        try {
            $student = $event->getStudent();
            $hostelName = $event->getHostelName() ?? 'assigned hostel';
            $roomNumber = $event->getRoomNumber() ?? 'assigned room';

            $message = "Hostel room assigned for {$student->full_name}. " .
                      "Hostel: {$hostelName}, Room: {$roomNumber}.";

            if ($student->user) {
                $this->notificationService->send(
                    $student->user->id,
                    'hostel_assignment',
                    [
                        'type' => 'hostel_assignment',
                        'title' => 'Hostel Room Assigned',
                        'message' => $message,
                        'data' => [
                            'hostel_id' => $event->hostelId,
                            'room_id' => $event->roomId,
                        ],
                    ],
                    ['database', 'email', 'sms']
                );
            }

            $parentContacts = $event->getParentContacts();
            foreach ($parentContacts as $contact) {
                if (!empty($contact['phone'])) {
                    $this->notificationService->sendToExternal([
                        'type' => 'hostel_assignment',
                        'message' => $message,
                        'recipient_phone' => $contact['phone'],
                    ], ['sms']);
                }
            }

            Log::info('Hostel assignment notification sent', [
                'student_id' => $student->id,
                'hostel_id' => $event->hostelId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send hostel assignment notification', [
                'student_id' => $event->student->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(HostelAssigned $event, \Throwable $exception): void
    {
        Log::error('Hostel assignment notification job failed', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
