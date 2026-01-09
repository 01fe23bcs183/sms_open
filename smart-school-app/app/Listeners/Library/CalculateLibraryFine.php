<?php

namespace App\Listeners\Library;

use App\Events\LibraryBookReturned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Calculate Library Fine Listener
 * 
 * Prompt 472: Create Library Event Listeners
 * 
 * Calculates and records fine if book is returned late.
 * Updates the library issue record with fine amount.
 */
class CalculateLibraryFine implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The fine per day for overdue books.
     */
    protected float $finePerDay = 5.00;

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
    public function handle(LibraryBookReturned $event): void
    {
        try {
            if (!$event->isOverdue()) {
                return;
            }

            $libraryIssue = $event->getLibraryIssue();
            $daysOverdue = $event->getDaysOverdue();

            $fineAmount = $daysOverdue * $this->finePerDay;

            if ($libraryIssue->fine_amount != $fineAmount) {
                DB::table('library_issues')
                    ->where('id', $libraryIssue->id)
                    ->update([
                        'fine_amount' => $fineAmount,
                        'updated_at' => now(),
                    ]);
            }

            Log::info('Library fine calculated', [
                'issue_id' => $libraryIssue->id,
                'days_overdue' => $daysOverdue,
                'fine_amount' => $fineAmount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to calculate library fine', [
                'issue_id' => $event->libraryIssue->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(LibraryBookReturned $event, \Throwable $exception): void
    {
        Log::error('Library fine calculation job failed', [
            'issue_id' => $event->libraryIssue->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
