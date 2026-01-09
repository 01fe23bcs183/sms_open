<?php

namespace App\Events;

use App\Models\FeesTransaction;
use App\Models\Student;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fees Payment Completed Event
 * 
 * Prompt 461: Create Fees Payment Completed Event
 * 
 * Triggered after a successful fee payment is processed.
 * Sends receipt, updates accounting ledger, and logs the transaction.
 * 
 * Listeners:
 * - SendPaymentConfirmation: Sends payment confirmation notification
 * - GenerateFeeReceipt: Generates PDF receipt for the payment
 * - UpdateLedgerEntry: Posts ledger entry for accounting
 * - LogFeesPayment: Creates audit log entry for the payment
 */
class FeesPaymentCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The fees transaction model instance.
     */
    public FeesTransaction $transaction;

    /**
     * The student model instance.
     */
    public Student $student;

    /**
     * The payment details.
     */
    public array $paymentDetails;

    /**
     * The updated fee summary for the student.
     */
    public array $feeSummary;

    /**
     * The user who processed the payment.
     */
    public array $processedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(
        FeesTransaction $transaction,
        Student $student,
        array $paymentDetails = [],
        array $feeSummary = [],
        array $processedBy = []
    ) {
        $this->transaction = $transaction;
        $this->student = $student;
        $this->paymentDetails = $paymentDetails;
        $this->feeSummary = $feeSummary;
        $this->processedBy = $processedBy;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("student.{$this->student->id}"),
            new PrivateChannel('fees'),
            new PrivateChannel('admin'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'fees.payment.completed';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'receipt_number' => $this->transaction->receipt_number,
            'student_id' => $this->student->id,
            'student_name' => $this->student->full_name,
            'amount_paid' => $this->transaction->amount,
            'payment_method' => $this->transaction->payment_method,
            'fee_summary' => $this->feeSummary,
            'processed_by' => $this->processedBy,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the transaction instance.
     */
    public function getTransaction(): FeesTransaction
    {
        return $this->transaction;
    }

    /**
     * Get the student instance.
     */
    public function getStudent(): Student
    {
        return $this->student;
    }

    /**
     * Get the amount paid.
     */
    public function getAmountPaid(): float
    {
        return $this->transaction->amount ?? 0.0;
    }

    /**
     * Get the payment method.
     */
    public function getPaymentMethod(): string
    {
        return $this->transaction->payment_method ?? 'cash';
    }

    /**
     * Get the receipt number.
     */
    public function getReceiptNumber(): ?string
    {
        return $this->transaction->receipt_number;
    }

    /**
     * Get the remaining balance.
     */
    public function getRemainingBalance(): float
    {
        return $this->feeSummary['remaining_balance'] ?? 0.0;
    }

    /**
     * Check if this payment clears all dues.
     */
    public function isFullPayment(): bool
    {
        return $this->getRemainingBalance() <= 0;
    }

    /**
     * Get parent contact information for notifications.
     */
    public function getParentContacts(): array
    {
        return [
            'father' => [
                'name' => $this->student->father_name,
                'phone' => $this->student->father_phone,
                'email' => $this->student->father_email,
            ],
            'mother' => [
                'name' => $this->student->mother_name,
                'phone' => $this->student->mother_phone,
                'email' => $this->student->mother_email,
            ],
            'guardian' => [
                'name' => $this->student->guardian_name,
                'phone' => $this->student->guardian_phone,
                'email' => $this->student->guardian_email,
            ],
        ];
    }
}
