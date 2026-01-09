<?php

namespace App\Listeners\Library;

use App\Events\LibraryBookReturned;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send Book Returned Notification Listener
 * 
 * Prompt 472: Create Library Event Listeners
 * 
 * Sends notification to library member when a book is returned.
 * Includes return confirmation and any fine information.
 */
class SendBookReturnedNotification implements ShouldQueue
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
    public function handle(LibraryBookReturned $event): void
    {
        try {
            $book = $event->getBook();
            $memberName = $event->getMemberName();

            $message = "Dear {$memberName}, '{$book->title}' has been returned successfully. ";

            if ($event->isOverdue()) {
                $fineAmount = number_format($event->getFineAmount(), 2);
                $daysOverdue = $event->getDaysOverdue();
                $message .= "The book was {$daysOverdue} day(s) overdue. Fine: Rs. {$fineAmount}. ";

                if ($event->isFinePaid()) {
                    $message .= "Fine has been paid. Thank you!";
                } else {
                    $message .= "Please pay the fine at the library counter.";
                }
            } else {
                $message .= "Thank you for returning on time!";
            }

            $notificationData = [
                'type' => 'book_returned',
                'title' => 'Book Returned',
                'message' => $message,
                'data' => [
                    'issue_id' => $event->libraryIssue->id,
                    'book_id' => $book->id,
                    'book_title' => $book->title,
                    'is_overdue' => $event->isOverdue(),
                    'fine_amount' => $event->getFineAmount(),
                    'fine_paid' => $event->isFinePaid(),
                ],
            ];

            $memberId = $event->memberDetails['user_id'] ?? null;
            if ($memberId) {
                $this->notificationService->send(
                    $memberId,
                    'book_returned',
                    $notificationData,
                    ['database']
                );
            }

            Log::info('Book returned notification sent', [
                'issue_id' => $event->libraryIssue->id,
                'book_id' => $book->id,
                'member_name' => $memberName,
                'is_overdue' => $event->isOverdue(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send book returned notification', [
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
        Log::error('Book returned notification job failed', [
            'issue_id' => $event->libraryIssue->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
