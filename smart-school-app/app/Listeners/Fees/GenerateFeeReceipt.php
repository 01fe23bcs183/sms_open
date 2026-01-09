<?php

namespace App\Listeners\Fees;

use App\Events\FeesPaymentCompleted;
use App\Jobs\GenerateReportJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Generate Fee Receipt Listener
 * 
 * Prompt 471: Create Fees Event Listeners
 * 
 * Generates a PDF receipt for the fee payment.
 * Dispatches a job to generate the receipt asynchronously.
 */
class GenerateFeeReceipt implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FeesPaymentCompleted $event): void
    {
        try {
            $student = $event->getStudent();
            $transaction = $event->getTransaction();

            GenerateReportJob::dispatch([
                'type' => 'fee_receipt',
                'transaction_id' => $transaction->id,
                'student_id' => $student->id,
                'data' => [
                    'receipt_number' => $event->getReceiptNumber(),
                    'student_name' => $student->full_name,
                    'admission_number' => $student->admission_number,
                    'class' => $student->schoolClass->name ?? 'N/A',
                    'section' => $student->section->name ?? 'N/A',
                    'amount_paid' => $event->getAmountPaid(),
                    'payment_method' => $event->getPaymentMethod(),
                    'payment_date' => $transaction->payment_date ?? now()->format('Y-m-d'),
                    'payment_details' => $event->paymentDetails,
                    'fee_summary' => $event->feeSummary,
                ],
            ]);

            Log::info('Fee receipt generation dispatched', [
                'student_id' => $student->id,
                'transaction_id' => $transaction->id,
                'receipt_number' => $event->getReceiptNumber(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch fee receipt generation', [
                'student_id' => $event->student->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(FeesPaymentCompleted $event, \Throwable $exception): void
    {
        Log::error('Fee receipt generation job failed', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
