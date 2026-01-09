<?php

namespace App\Listeners\Fees;

use App\Events\FeesPaymentCompleted;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log Fees Payment Listener
 * 
 * Prompt 471: Create Fees Event Listeners
 * 
 * Creates an audit log entry for fee payments.
 * Records payment details for compliance and tracking.
 */
class LogFeesPayment implements ShouldQueue
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
    public function handle(FeesPaymentCompleted $event): void
    {
        try {
            $student = $event->getStudent();
            $transaction = $event->getTransaction();

            $this->auditLogService->log(
                'fee_payment',
                'Fee payment processed',
                [
                    'student_id' => $student->id,
                    'admission_number' => $student->admission_number,
                    'transaction_id' => $transaction->id,
                    'receipt_number' => $event->getReceiptNumber(),
                    'amount_paid' => $event->getAmountPaid(),
                    'payment_method' => $event->getPaymentMethod(),
                    'remaining_balance' => $event->getRemainingBalance(),
                    'is_full_payment' => $event->isFullPayment(),
                    'payment_details' => $event->paymentDetails,
                ],
                $event->processedBy['id'] ?? null,
                'fees_transactions',
                $transaction->id
            );

            Log::info('Fee payment logged', [
                'student_id' => $student->id,
                'transaction_id' => $transaction->id,
                'amount' => $event->getAmountPaid(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log fee payment', [
                'student_id' => $event->student->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(FeesPaymentCompleted $event, \Throwable $exception): void
    {
        Log::error('Fee payment logging job failed', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
