<?php

namespace App\Listeners\Library;

use App\Events\LibraryBookReturned;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log Book Returned Listener
 * 
 * Prompt 472: Create Library Event Listeners
 * 
 * Creates an audit log entry when a book is returned.
 * Records return details including any fines for tracking.
 */
class LogBookReturned implements ShouldQueue
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
    public function handle(LibraryBookReturned $event): void
    {
        try {
            $book = $event->getBook();
            $libraryIssue = $event->getLibraryIssue();

            $this->auditLogService->log(
                'book_returned',
                'Library book returned',
                [
                    'issue_id' => $libraryIssue->id,
                    'book_id' => $book->id,
                    'book_title' => $book->title,
                    'member_details' => $event->memberDetails,
                    'return_date' => $event->getReturnDate(),
                    'is_overdue' => $event->isOverdue(),
                    'days_overdue' => $event->getDaysOverdue(),
                    'fine_amount' => $event->getFineAmount(),
                    'fine_paid' => $event->isFinePaid(),
                ],
                $event->processedBy['id'] ?? null,
                'library_issues',
                $libraryIssue->id
            );

            Log::info('Book return logged', [
                'issue_id' => $libraryIssue->id,
                'book_id' => $book->id,
                'is_overdue' => $event->isOverdue(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log book return', [
                'issue_id' => $event->libraryIssue->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(LibraryBookReturned $event, \Throwable $exception): void
    {
        Log::error('Book return logging job failed', [
            'issue_id' => $event->libraryIssue->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
