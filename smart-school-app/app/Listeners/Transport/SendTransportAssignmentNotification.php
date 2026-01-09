<?php

namespace App\Listeners\Transport;

use App\Events\TransportAssigned;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send Transport Assignment Notification Listener
 * 
 * Sends notification to student/parents when transport is assigned.
 */
class SendTransportAssignmentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 60;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(TransportAssigned $event): void
    {
        try {
            $student = $event->getStudent();
            $routeName = $event->getRouteName() ?? 'assigned route';
            $stopName = $event->getStopName() ?? 'assigned stop';
            $pickupTime = $event->getPickupTime() ?? 'TBD';

            $message = "Transport assigned for {$student->full_name}. " .
                      "Route: {$routeName}, Stop: {$stopName}, Pickup Time: {$pickupTime}.";

            if ($student->user) {
                $this->notificationService->send(
                    $student->user->id,
                    'transport_assignment',
                    [
                        'type' => 'transport_assignment',
                        'title' => 'Transport Assigned',
                        'message' => $message,
                        'data' => [
                            'route_id' => $event->routeId,
                            'vehicle_id' => $event->vehicleId,
                            'stop_id' => $event->stopId,
                        ],
                    ],
                    ['database', 'email', 'sms']
                );
            }

            $parentContacts = $event->getParentContacts();
            foreach ($parentContacts as $contact) {
                if (!empty($contact['phone'])) {
                    $this->notificationService->sendToExternal([
                        'type' => 'transport_assignment',
                        'message' => $message,
                        'recipient_phone' => $contact['phone'],
                    ], ['sms']);
                }
            }

            Log::info('Transport assignment notification sent', [
                'student_id' => $student->id,
                'route_id' => $event->routeId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send transport assignment notification', [
                'student_id' => $event->student->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(TransportAssigned $event, \Throwable $exception): void
    {
        Log::error('Transport assignment notification job failed', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
