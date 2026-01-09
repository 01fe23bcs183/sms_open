<?php

namespace App\Listeners\Fees;

use App\Events\FeesPaymentCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Update Ledger Entry Listener
 * 
 * Prompt 471: Create Fees Event Listeners
 * 
 * Posts a ledger entry for the fee payment.
 * Updates accounting records and income tracking.
 */
class UpdateLedgerEntry implements ShouldQueue
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

            $incomeExists = DB::table('information_schema.tables')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', 'income')
                ->exists();

            if ($incomeExists) {
                DB::table('income')->insert([
                    'income_category_id' => $this->getFeeIncomeCategoryId(),
                    'name' => "Fee Payment - {$student->admission_number}",
                    'amount' => $event->getAmountPaid(),
                    'date' => now()->format('Y-m-d'),
                    'payment_method' => $event->getPaymentMethod(),
                    'reference_number' => $event->getReceiptNumber(),
                    'description' => "Fee payment from {$student->full_name} (Transaction ID: {$transaction->id})",
                    'created_by' => $event->processedBy['id'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $cacheKeys = [
                'accounting.income.summary',
                'accounting.income.daily.' . now()->format('Y-m-d'),
                'accounting.income.monthly.' . now()->format('Y-m'),
                'dashboard.finance.summary',
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            Cache::tags(['accounting', 'income', 'fees'])->flush();

            Log::info('Ledger entry updated', [
                'student_id' => $student->id,
                'transaction_id' => $transaction->id,
                'amount' => $event->getAmountPaid(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update ledger entry', [
                'student_id' => $event->student->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get the income category ID for fee payments.
     */
    protected function getFeeIncomeCategoryId(): ?int
    {
        $category = DB::table('income_categories')
            ->where('name', 'like', '%fee%')
            ->orWhere('code', 'FEE')
            ->first();

        return $category->id ?? null;
    }

    /**
     * Handle a job failure.
     */
    public function failed(FeesPaymentCompleted $event, \Throwable $exception): void
    {
        Log::error('Ledger entry update job failed', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
