<?php

namespace App\Listeners\Fees;

use App\Events\FeesPaymentCompleted;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send Payment Confirmation Listener
 * 
 * Prompt 471: Create Fees Event Listeners
 * 
 * Sends payment confirmation notification to students and parents.
 * Includes receipt number, amount paid, and remaining balance.
 */
class SendPaymentConfirmation implements ShouldQueue
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
    public function handle(FeesPaymentCompleted $event): void
    {
        try {
            $student = $event->getStudent();
            $amount = number_format($event->getAmountPaid(), 2);
            $receiptNumber = $event->getReceiptNumber();
            $remainingBalance = number_format($event->getRemainingBalance(), 2);

            $message = "Payment of Rs. {$amount} received for {$student->full_name}. " .
                      "Receipt No: {$receiptNumber}. ";

            if ($event->isFullPayment()) {
                $message .= "All dues cleared. Thank you!";
            } else {
                $message .= "Remaining Balance: Rs. {$remainingBalance}.";
            }

            $notificationData = [
                'type' => 'payment_confirmation',
                'title' => 'Payment Received',
                'message' => $message,
                'data' => [
                    'student_id' => $student->id,
                    'transaction_id' => $event->transaction->id,
                    'receipt_number' => $receiptNumber,
                    'amount_paid' => $event->getAmountPaid(),
                    'payment_method' => $event->getPaymentMethod(),
                    'remaining_balance' => $event->getRemainingBalance(),
                ],
            ];

            if ($student->user) {
                $this->notificationService->send(
                    $student->user->id,
                    'payment_confirmation',
                    $notificationData,
                    ['database', 'email', 'sms']
                );
            }

            $parentContacts = $event->getParentContacts();
            foreach ($parentContacts as $type => $contact) {
                if (!empty($contact['email']) || !empty($contact['phone'])) {
                    $this->sendParentNotification($contact, $message);
                }
            }

            Log::info('Payment confirmation sent', [
                'student_id' => $student->id,
                'transaction_id' => $event->transaction->id,
                'amount' => $event->getAmountPaid(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation', [
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
            'type' => 'payment_confirmation_parent',
            'title' => 'Payment Received',
            'message' => $message,
            'recipient_email' => $contact['email'],
            'recipient_phone' => $contact['phone'],
        ];

        $this->notificationService->sendToExternal($notificationData, $channels);
    }

    /**
     * Handle a job failure.
     */
    public function failed(FeesPaymentCompleted $event, \Throwable $exception): void
    {
        Log::error('Payment confirmation job failed', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
