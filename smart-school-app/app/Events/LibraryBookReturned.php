<?php

namespace App\Events;

use App\Models\LibraryBook;
use App\Models\LibraryIssue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Library Book Returned Event
 * 
 * Prompt 463: Create Library Book Returned Event
 * 
 * Triggered when a book is returned to the library.
 * Updates stock counts, calculates fines if late, and closes issue records.
 * 
 * Listeners:
 * - SendBookReturnedNotification: Sends return confirmation notification
 * - CalculateLibraryFine: Calculates and records fine if book is overdue
 * - UpdateBookStock: Increases available stock count
 * - LogBookReturned: Creates audit log entry for the book return
 */
class LibraryBookReturned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The library issue model instance.
     */
    public LibraryIssue $libraryIssue;

    /**
     * The library book model instance.
     */
    public LibraryBook $book;

    /**
     * The member details.
     */
    public array $memberDetails;

    /**
     * The return details.
     */
    public array $returnDetails;

    /**
     * The user who processed the return.
     */
    public array $processedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(
        LibraryIssue $libraryIssue,
        LibraryBook $book,
        array $memberDetails = [],
        array $returnDetails = [],
        array $processedBy = []
    ) {
        $this->libraryIssue = $libraryIssue;
        $this->book = $book;
        $this->memberDetails = $memberDetails;
        $this->returnDetails = $returnDetails;
        $this->processedBy = $processedBy;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('library'),
            new PrivateChannel('admin'),
        ];

        if (isset($this->memberDetails['member_id'])) {
            $channels[] = new PrivateChannel("library.member.{$this->memberDetails['member_id']}");
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'library.book.returned';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'issue_id' => $this->libraryIssue->id,
            'book_id' => $this->book->id,
            'book_title' => $this->book->title,
            'member_details' => $this->memberDetails,
            'return_details' => $this->returnDetails,
            'is_overdue' => $this->isOverdue(),
            'fine_amount' => $this->getFineAmount(),
            'processed_by' => $this->processedBy,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the library issue instance.
     */
    public function getLibraryIssue(): LibraryIssue
    {
        return $this->libraryIssue;
    }

    /**
     * Get the book instance.
     */
    public function getBook(): LibraryBook
    {
        return $this->book;
    }

    /**
     * Check if the book was returned late.
     */
    public function isOverdue(): bool
    {
        return $this->returnDetails['is_overdue'] ?? false;
    }

    /**
     * Get the number of days overdue.
     */
    public function getDaysOverdue(): int
    {
        return $this->returnDetails['days_overdue'] ?? 0;
    }

    /**
     * Get the fine amount.
     */
    public function getFineAmount(): float
    {
        return $this->returnDetails['fine_amount'] ?? $this->libraryIssue->fine_amount ?? 0.0;
    }

    /**
     * Check if fine was paid.
     */
    public function isFinePaid(): bool
    {
        return $this->returnDetails['fine_paid'] ?? $this->libraryIssue->fine_paid ?? false;
    }

    /**
     * Get the return date.
     */
    public function getReturnDate(): ?string
    {
        return $this->libraryIssue->return_date?->format('Y-m-d');
    }

    /**
     * Get the member name.
     */
    public function getMemberName(): ?string
    {
        return $this->memberDetails['name'] ?? null;
    }
}
