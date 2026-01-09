<?php

namespace App\Listeners\Library;

use App\Events\LibraryBookIssued;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send Book Issued Notification Listener
 * 
 * Prompt 472: Create Library Event Listeners
 * 
 * Sends notification to library member when a book is issued.
 * Includes book details and due date information.
 */
class SendBookIssuedNotification implements ShouldQueue
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
    public function handle(LibraryBookIssued $event): void
    {
        try {
            $book = $event->getBook();
            $memberName = $event->getMemberName();
            $dueDate = $event->getDueDate();
            $daysUntilDue = $event->getDaysUntilDue();

            $message = "Dear {$memberName}, you have borrowed '{$book->title}'. " .
                      "Due Date: {$dueDate} ({$daysUntilDue} days). " .
                      "Please return on time to avoid fines.";

            $notificationData = [
                'type' => 'book_issued',
                'title' => 'Book Issued',
                'message' => $message,
                'data' => [
                    'issue_id' => $event->libraryIssue->id,
                    'book_id' => $book->id,
                    'book_title' => $book->title,
                    'due_date' => $dueDate,
                    'days_until_due' => $daysUntilDue,
                ],
            ];

            $memberId = $event->memberDetails['user_id'] ?? null;
            if ($memberId) {
                $this->notificationService->send(
                    $memberId,
                    'book_issued',
                    $notificationData,
                    ['database', 'email']
                );
            }

            $memberEmail = $event->memberDetails['email'] ?? null;
            if ($memberEmail) {
                $this->notificationService->sendToExternal([
                    'type' => 'book_issued',
                    'title' => 'Book Issued',
                    'message' => $message,
                    'recipient_email' => $memberEmail,
                ], ['email']);
            }

            Log::info('Book issued notification sent', [
                'issue_id' => $event->libraryIssue->id,
                'book_id' => $book->id,
                'member_name' => $memberName,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send book issued notification', [
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
        Log::error('Book issued notification job failed', [
            'issue_id' => $event->libraryIssue->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
