<?php

namespace App\Events;

use App\Models\FeesAllotment;
use App\Models\Student;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fees Invoice Generated Event
 * 
 * Prompt 460: Create Fees Invoice Generated Event
 * 
 * Triggered when a new fee invoice is generated for a student.
 * Sends invoice alerts to parents and schedules due date reminders.
 * 
 * Listeners:
 * - SendFeeInvoiceNotification: Sends notification with invoice details and due date
 * - ScheduleFeeReminder: Schedules reminder jobs before due date
 */
class FeesInvoiceGenerated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The fees allotment model instance.
     */
    public FeesAllotment $feesAllotment;

    /**
     * The student model instance.
     */
    public Student $student;

    /**
     * The invoice details.
     */
    public array $invoiceDetails;

    /**
     * The user who generated the invoice.
     */
    public array $generatedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(
        FeesAllotment $feesAllotment,
        Student $student,
        array $invoiceDetails = [],
        array $generatedBy = []
    ) {
        $this->feesAllotment = $feesAllotment;
        $this->student = $student;
        $this->invoiceDetails = $invoiceDetails;
        $this->generatedBy = $generatedBy;
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
        return 'fees.invoice.generated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'allotment_id' => $this->feesAllotment->id,
            'student_id' => $this->student->id,
            'student_name' => $this->student->full_name,
            'admission_number' => $this->student->admission_number,
            'invoice_details' => $this->invoiceDetails,
            'generated_by' => $this->generatedBy,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the fees allotment instance.
     */
    public function getFeesAllotment(): FeesAllotment
    {
        return $this->feesAllotment;
    }

    /**
     * Get the student instance.
     */
    public function getStudent(): Student
    {
        return $this->student;
    }

    /**
     * Get the total amount due.
     */
    public function getTotalAmount(): float
    {
        return $this->invoiceDetails['total_amount'] ?? $this->feesAllotment->amount ?? 0.0;
    }

    /**
     * Get the due date.
     */
    public function getDueDate(): ?string
    {
        return $this->invoiceDetails['due_date'] ?? $this->feesAllotment->due_date?->format('Y-m-d');
    }

    /**
     * Get the fee type name.
     */
    public function getFeeTypeName(): ?string
    {
        return $this->invoiceDetails['fee_type'] ?? $this->feesAllotment->feesMaster?->feesType?->name;
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
