<?php

namespace App\Listeners\Fees;

use App\Events\FeesInvoiceGenerated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send Fee Invoice Notification Listener
 * 
 * Prompt 471: Create Fees Event Listeners
 * 
 * Sends notification to parents when a fee invoice is generated.
 * Includes invoice details, amount, and due date.
 */
class SendFeeInvoiceNotification implements ShouldQueue
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
    public function handle(FeesInvoiceGenerated $event): void
    {
        try {
            $student = $event->getStudent();
            $amount = number_format($event->getTotalAmount(), 2);
            $dueDate = $event->getDueDate();
            $feeType = $event->getFeeTypeName();

            $message = "Dear Parent, a fee invoice of Rs. {$amount} has been generated for {$student->full_name}. " .
                      "Fee Type: {$feeType}. Due Date: {$dueDate}. Please pay before the due date to avoid late fees.";

            $notificationData = [
                'type' => 'fee_invoice_generated',
                'title' => 'Fee Invoice Generated',
                'message' => $message,
                'data' => [
                    'student_id' => $student->id,
                    'allotment_id' => $event->feesAllotment->id,
                    'amount' => $event->getTotalAmount(),
                    'due_date' => $dueDate,
                    'fee_type' => $feeType,
                ],
            ];

            if ($student->user) {
                $this->notificationService->send(
                    $student->user->id,
                    'fee_invoice_generated',
                    $notificationData,
                    ['database', 'email']
                );
            }

            $parentContacts = $event->getParentContacts();
            foreach ($parentContacts as $type => $contact) {
                if (!empty($contact['email']) || !empty($contact['phone'])) {
                    $this->sendParentNotification($contact, $message);
                }
            }

            Log::info('Fee invoice notification sent', [
                'student_id' => $student->id,
                'allotment_id' => $event->feesAllotment->id,
                'amount' => $event->getTotalAmount(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send fee invoice notification', [
                'student_id' => $event->student->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Send notification to parent.
     */
    protected function sendParentNotification(array $contact, string $message): void
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
            'type' => 'fee_invoice_parent',
            'title' => 'Fee Invoice Generated',
            'message' => $message,
            'recipient_email' => $contact['email'],
            'recipient_phone' => $contact['phone'],
        ];

        $this->notificationService->sendToExternal($notificationData, $channels);
    }

    /**
     * Handle a job failure.
     */
    public function failed(FeesInvoiceGenerated $event, \Throwable $exception): void
    {
        Log::error('Fee invoice notification job failed', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
