<?php

namespace App\Listeners\Fees;

use App\Events\FeesInvoiceGenerated;
use App\Jobs\SendEmailJob;
use App\Jobs\SendSmsJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Schedule Fee Reminder Listener
 * 
 * Prompt 471: Create Fees Event Listeners
 * 
 * Schedules reminder jobs before the fee due date.
 * Sends reminders at 7 days, 3 days, and 1 day before due date.
 */
class ScheduleFeeReminder implements ShouldQueue
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
    public function handle(FeesInvoiceGenerated $event): void
    {
        try {
            $student = $event->getStudent();
            $dueDate = $event->getDueDate();

            if (!$dueDate) {
                Log::warning('No due date for fee reminder scheduling', [
                    'student_id' => $student->id,
                    'allotment_id' => $event->feesAllotment->id,
                ]);
                return;
            }

            $dueDateCarbon = \Carbon\Carbon::parse($dueDate);
            $now = now();

            $reminderDays = [7, 3, 1];

            foreach ($reminderDays as $days) {
                $reminderDate = $dueDateCarbon->copy()->subDays($days);

                if ($reminderDate->gt($now)) {
                    $this->scheduleReminder($event, $reminderDate, $days);
                }
            }

            Log::info('Fee reminders scheduled', [
                'student_id' => $student->id,
                'allotment_id' => $event->feesAllotment->id,
                'due_date' => $dueDate,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to schedule fee reminders', [
                'student_id' => $event->student->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Schedule a reminder for a specific date.
     */
    protected function scheduleReminder(FeesInvoiceGenerated $event, \Carbon\Carbon $reminderDate, int $daysBeforeDue): void
    {
        $student = $event->getStudent();
        $amount = number_format($event->getTotalAmount(), 2);
        $dueDate = $event->getDueDate();
        $feeType = $event->getFeeTypeName();

        $message = "Reminder: Fee payment of Rs. {$amount} for {$student->full_name} is due in {$daysBeforeDue} day(s) on {$dueDate}. " .
                  "Fee Type: {$feeType}. Please pay to avoid late fees.";

        $parentContacts = $event->getParentContacts();

        foreach ($parentContacts as $type => $contact) {
            if (!empty($contact['email'])) {
                SendEmailJob::dispatch([
                    'to' => $contact['email'],
                    'subject' => "Fee Payment Reminder - {$daysBeforeDue} Day(s) Left",
                    'body' => $message,
                    'template' => 'fee_reminder',
                    'data' => [
                        'student_name' => $student->full_name,
                        'amount' => $event->getTotalAmount(),
                        'due_date' => $dueDate,
                        'fee_type' => $feeType,
                        'days_remaining' => $daysBeforeDue,
                    ],
                ])->delay($reminderDate);
            }

            if (!empty($contact['phone'])) {
                SendSmsJob::dispatch([
                    'to' => $contact['phone'],
                    'message' => $message,
                    'template' => 'fee_reminder',
                ])->delay($reminderDate);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(FeesInvoiceGenerated $event, \Throwable $exception): void
    {
        Log::error('Fee reminder scheduling job failed', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
