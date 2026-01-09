<?php

namespace App\Listeners\Library;

use App\Events\LibraryBookIssued;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log Book Issued Listener
 * 
 * Prompt 472: Create Library Event Listeners
 * 
 * Creates an audit log entry when a book is issued.
 * Records issue details for tracking and compliance.
 */
class LogBookIssued implements ShouldQueue
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
    public function handle(LibraryBookIssued $event): void
    {
        try {
            $book = $event->getBook();
            $libraryIssue = $event->getLibraryIssue();

            $this->auditLogService->log(
                'book_issued',
                'Library book issued',
                [
                    'issue_id' => $libraryIssue->id,
                    'book_id' => $book->id,
                    'book_title' => $book->title,
                    'book_isbn' => $book->isbn,
                    'member_details' => $event->memberDetails,
                    'issue_date' => $event->getIssueDate(),
                    'due_date' => $event->getDueDate(),
                ],
                $event->issuedBy['id'] ?? null,
                'library_issues',
                $libraryIssue->id
            );

            Log::info('Book issue logged', [
                'issue_id' => $libraryIssue->id,
                'book_id' => $book->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log book issue', [
                'issue_id' => $event->libraryIssue->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(LibraryBookIssued $event, \Throwable $exception): void
    {
        Log::error('Book issue logging job failed', [
            'issue_id' => $event->libraryIssue->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
