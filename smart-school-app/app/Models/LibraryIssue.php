<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LibraryIssue Model
 * 
 * Prompt 86: Create LibraryIssue Model with relationships to LibraryBook,
 * LibraryMember, User (issued_by), User (returned_by).
 * 
 * @property int $id
 * @property int $book_id
 * @property int $member_id
 * @property \Carbon\Carbon $issue_date
 * @property \Carbon\Carbon $due_date
 * @property \Carbon\Carbon|null $return_date
 * @property float $fine_amount
 * @property bool $fine_paid
 * @property string|null $remarks
 * @property int|null $issued_by
 * @property int|null $returned_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class LibraryIssue extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'library_issues';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'book_id',
        'member_id',
        'issue_date',
        'due_date',
        'return_date',
        'fine_amount',
        'fine_paid',
        'remarks',
        'issued_by',
        'returned_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
            'fine_amount' => 'decimal:2',
            'fine_paid' => 'boolean',
        ];
    }

    /**
     * Get the book for this issue.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class, 'book_id');
    }

    /**
     * Get the member for this issue.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(LibraryMember::class, 'member_id');
    }

    /**
     * Get the user who issued this book.
     */
    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Get the user who received the returned book.
     */
    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    /**
     * Scope a query to filter by book.
     */
    public function scopeForBook($query, int $bookId)
    {
        return $query->where('book_id', $bookId);
    }

    /**
     * Scope a query to filter by member.
     */
    public function scopeForMember($query, int $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    /**
     * Scope a query to filter currently issued books.
     */
    public function scopeCurrentlyIssued($query)
    {
        return $query->whereNull('return_date');
    }

    /**
     * Scope a query to filter returned books.
     */
    public function scopeReturned($query)
    {
        return $query->whereNotNull('return_date');
    }

    /**
     * Scope a query to filter overdue books.
     */
    public function scopeOverdue($query)
    {
        return $query->whereNull('return_date')
                     ->where('due_date', '<', now());
    }

    /**
     * Scope a query to filter books due today.
     */
    public function scopeDueToday($query)
    {
        return $query->whereNull('return_date')
                     ->whereDate('due_date', now());
    }

    /**
     * Scope a query to filter books with unpaid fines.
     */
    public function scopeWithUnpaidFine($query)
    {
        return $query->where('fine_amount', '>', 0)
                     ->where('fine_paid', false);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeIssuedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('issue_date', [$startDate, $endDate]);
    }

    /**
     * Check if this book is currently issued.
     */
    public function isCurrentlyIssued(): bool
    {
        return is_null($this->return_date);
    }

    /**
     * Check if this book has been returned.
     */
    public function isReturned(): bool
    {
        return !is_null($this->return_date);
    }

    /**
     * Check if this book is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->isCurrentlyIssued() && $this->due_date->isPast();
    }

    /**
     * Check if this book is due today.
     */
    public function isDueToday(): bool
    {
        return $this->isCurrentlyIssued() && $this->due_date->isToday();
    }

    /**
     * Check if there is an unpaid fine.
     */
    public function hasUnpaidFine(): bool
    {
        return $this->fine_amount > 0 && !$this->fine_paid;
    }

    /**
     * Get the number of days overdue.
     */
    public function getDaysOverdueAttribute(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return $this->due_date->diffInDays(now());
    }

    /**
     * Get the number of days until due.
     */
    public function getDaysUntilDueAttribute(): int
    {
        if ($this->isReturned() || $this->isOverdue()) {
            return 0;
        }
        return now()->diffInDays($this->due_date);
    }

    /**
     * Get the issue status.
     */
    public function getStatusAttribute(): string
    {
        if ($this->isReturned()) {
            return 'Returned';
        }
        if ($this->isOverdue()) {
            return 'Overdue';
        }
        if ($this->isDueToday()) {
            return 'Due Today';
        }
        return 'Issued';
    }

    /**
     * Calculate fine based on overdue days.
     */
    public function calculateFine(float $finePerDay = 1.00): float
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return $this->days_overdue * $finePerDay;
    }

    /**
     * Return the book and calculate fine.
     */
    public function returnBook(int $returnedById, float $finePerDay = 1.00): bool
    {
        if ($this->isReturned()) {
            return false;
        }

        $this->return_date = now();
        $this->returned_by = $returnedById;
        
        if ($this->isOverdue()) {
            $this->fine_amount = $this->calculateFine($finePerDay);
        }

        $this->save();

        $this->book->return();

        return true;
    }
}
