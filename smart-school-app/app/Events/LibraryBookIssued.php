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
 * Library Book Issued Event
 * 
 * Prompt 462: Create Library Book Issued Event
 * 
 * Triggered when a book is issued to a library member.
 * Updates stock counts and sends issue notification with due date.
 * 
 * Listeners:
 * - SendBookIssuedNotification: Sends notification with book details and due date
 * - UpdateBookStock: Reduces available stock count
 * - LogBookIssued: Creates audit log entry for the book issue
 */
class LibraryBookIssued implements ShouldBroadcast
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
     * The user who issued the book.
     */
    public array $issuedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(
        LibraryIssue $libraryIssue,
        LibraryBook $book,
        array $memberDetails = [],
        array $issuedBy = []
    ) {
        $this->libraryIssue = $libraryIssue;
        $this->book = $book;
        $this->memberDetails = $memberDetails;
        $this->issuedBy = $issuedBy;
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
        return 'library.book.issued';
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
            'book_isbn' => $this->book->isbn,
            'member_details' => $this->memberDetails,
            'issue_date' => $this->libraryIssue->issue_date,
            'due_date' => $this->libraryIssue->due_date,
            'issued_by' => $this->issuedBy,
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
     * Get the due date.
     */
    public function getDueDate(): ?string
    {
        return $this->libraryIssue->due_date?->format('Y-m-d');
    }

    /**
     * Get the issue date.
     */
    public function getIssueDate(): ?string
    {
        return $this->libraryIssue->issue_date?->format('Y-m-d');
    }

    /**
     * Get the number of days until due.
     */
    public function getDaysUntilDue(): int
    {
        if (!$this->libraryIssue->due_date) {
            return 0;
        }
        return now()->diffInDays($this->libraryIssue->due_date, false);
    }

    /**
     * Get the member name.
     */
    public function getMemberName(): ?string
    {
        return $this->memberDetails['name'] ?? null;
    }

    /**
     * Get the member type (student, teacher, staff).
     */
    public function getMemberType(): ?string
    {
        return $this->memberDetails['type'] ?? null;
    }
}
