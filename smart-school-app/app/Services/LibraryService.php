<?php

namespace App\Services;

use App\Models\LibraryBook;
use App\Models\LibraryCategory;
use App\Models\LibraryMember;
use App\Models\LibraryIssue;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

/**
 * Library Service
 * 
 * Prompt 331: Create Library Service
 * Prompt 411: Implement Library Book Cover Upload
 * 
 * Manages library inventory and issue rules. Handles book stock,
 * issue, and return flows. Validates issue limits, calculates fines,
 * and handles book cover image uploads.
 */
class LibraryService
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Default fine per day for late returns.
     */
    private const DEFAULT_FINE_PER_DAY = 1.00;

    /**
     * Default loan period in days.
     */
    private const DEFAULT_LOAN_DAYS = 14;

    /**
     * Add a new book to the library.
     * 
     * @param array $data
     * @return LibraryBook
     */
    public function addBook(array $data): LibraryBook
    {
        return LibraryBook::create([
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'author' => $data['author'],
            'isbn' => $data['isbn'] ?? null,
            'publisher' => $data['publisher'] ?? null,
            'edition' => $data['edition'] ?? null,
            'publication_year' => $data['publication_year'] ?? null,
            'quantity' => $data['quantity'] ?? 1,
            'available_quantity' => $data['quantity'] ?? 1,
            'price' => $data['price'] ?? null,
            'rack_number' => $data['rack_number'] ?? null,
            'description' => $data['description'] ?? null,
            'cover_image' => $data['cover_image'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update a book.
     * 
     * @param LibraryBook $book
     * @param array $data
     * @return LibraryBook
     */
    public function updateBook(LibraryBook $book, array $data): LibraryBook
    {
        // Adjust available quantity if total quantity changed
        if (isset($data['quantity'])) {
            $issuedCount = $book->quantity - $book->available_quantity;
            $data['available_quantity'] = $data['quantity'] - $issuedCount;
            
            if ($data['available_quantity'] < 0) {
                throw new \Exception('Cannot reduce quantity below issued count.');
            }
        }
        
        $book->update($data);
        return $book->fresh();
    }

    /**
     * Create a library member.
     * 
     * @param array $data
     * @return LibraryMember
     */
    public function createMember(array $data): LibraryMember
    {
        return LibraryMember::create([
            'member_type' => $data['member_type'], // 'student', 'teacher', 'staff'
            'member_id' => $data['member_id'],
            'membership_number' => $data['membership_number'] ?? $this->generateMembershipNumber(),
            'max_books' => $data['max_books'] ?? 5,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Issue a book to a member.
     * 
     * @param int $bookId
     * @param int $memberId
     * @param int|null $issuedBy
     * @param int|null $loanDays
     * @return LibraryIssue
     * @throws \Exception
     */
    public function issueBook(
        int $bookId,
        int $memberId,
        ?int $issuedBy = null,
        ?int $loanDays = null
    ): LibraryIssue {
        return DB::transaction(function () use ($bookId, $memberId, $issuedBy, $loanDays) {
            $book = LibraryBook::findOrFail($bookId);
            $member = LibraryMember::findOrFail($memberId);
            
            // Check if book is available
            if ($book->available_quantity <= 0) {
                throw new \Exception('Book is not available for issue.');
            }
            
            // Check member's issue limit
            $currentIssues = LibraryIssue::where('member_id', $memberId)
                ->whereNull('return_date')
                ->count();
            
            if ($currentIssues >= $member->max_books) {
                throw new \Exception("Member has reached maximum book limit ({$member->max_books}).");
            }
            
            // Check if member already has this book
            $alreadyIssued = LibraryIssue::where('member_id', $memberId)
                ->where('book_id', $bookId)
                ->whereNull('return_date')
                ->exists();
            
            if ($alreadyIssued) {
                throw new \Exception('Member already has this book issued.');
            }
            
            // Create issue record
            $loanDays = $loanDays ?? self::DEFAULT_LOAN_DAYS;
            $issue = LibraryIssue::create([
                'book_id' => $bookId,
                'member_id' => $memberId,
                'issue_date' => now(),
                'due_date' => now()->addDays($loanDays),
                'issued_by' => $issuedBy,
            ]);
            
            // Update book availability
            $book->decrement('available_quantity');
            
            return $issue->load(['book', 'member']);
        });
    }

    /**
     * Return a book.
     * 
     * @param int $issueId
     * @param int|null $returnedBy
     * @param float|null $finePerDay
     * @return LibraryIssue
     */
    public function returnBook(
        int $issueId,
        ?int $returnedBy = null,
        ?float $finePerDay = null
    ): LibraryIssue {
        return DB::transaction(function () use ($issueId, $returnedBy, $finePerDay) {
            $issue = LibraryIssue::with('book')->findOrFail($issueId);
            
            if ($issue->return_date) {
                throw new \Exception('Book has already been returned.');
            }
            
            $finePerDay = $finePerDay ?? self::DEFAULT_FINE_PER_DAY;
            $fineAmount = 0;
            
            // Calculate fine if overdue
            if ($issue->due_date->isPast()) {
                $daysOverdue = $issue->due_date->diffInDays(now());
                $fineAmount = $daysOverdue * $finePerDay;
            }
            
            // Update issue record
            $issue->update([
                'return_date' => now(),
                'returned_by' => $returnedBy,
                'fine_amount' => $fineAmount,
            ]);
            
            // Update book availability
            $issue->book->increment('available_quantity');
            
            return $issue->fresh(['book', 'member']);
        });
    }

    /**
     * Renew a book issue.
     * 
     * @param int $issueId
     * @param int|null $additionalDays
     * @return LibraryIssue
     */
    public function renewBook(int $issueId, ?int $additionalDays = null): LibraryIssue
    {
        $issue = LibraryIssue::findOrFail($issueId);
        
        if ($issue->return_date) {
            throw new \Exception('Cannot renew a returned book.');
        }
        
        $additionalDays = $additionalDays ?? self::DEFAULT_LOAN_DAYS;
        $newDueDate = $issue->due_date->addDays($additionalDays);
        
        $issue->update(['due_date' => $newDueDate]);
        
        return $issue->fresh();
    }

    /**
     * Pay fine for an issue.
     * 
     * @param int $issueId
     * @return LibraryIssue
     */
    public function payFine(int $issueId): LibraryIssue
    {
        $issue = LibraryIssue::findOrFail($issueId);
        
        if ($issue->fine_amount <= 0) {
            throw new \Exception('No fine to pay.');
        }
        
        $issue->update(['fine_paid' => true]);
        
        return $issue->fresh();
    }

    /**
     * Get all books.
     * 
     * @param int|null $categoryId
     * @param bool $availableOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBooks(?int $categoryId = null, bool $availableOnly = false)
    {
        $query = LibraryBook::with('category')->where('is_active', true);
        
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        if ($availableOnly) {
            $query->where('available_quantity', '>', 0);
        }
        
        return $query->orderBy('title')->get();
    }

    /**
     * Search books.
     * 
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchBooks(string $search)
    {
        return LibraryBook::with('category')
            ->where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get();
    }

    /**
     * Get book categories.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategories()
    {
        return LibraryCategory::where('is_active', true)->orderBy('name')->get();
    }

    /**
     * Get member's current issues.
     * 
     * @param int $memberId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMemberIssues(int $memberId)
    {
        return LibraryIssue::with(['book', 'issuedBy'])
            ->where('member_id', $memberId)
            ->whereNull('return_date')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get member's issue history.
     * 
     * @param int $memberId
     * @param int|null $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMemberHistory(int $memberId, ?int $limit = null)
    {
        $query = LibraryIssue::with(['book', 'issuedBy', 'returnedBy'])
            ->where('member_id', $memberId)
            ->orderBy('issue_date', 'desc');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    /**
     * Get overdue books.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOverdueBooks()
    {
        return LibraryIssue::with(['book', 'member'])
            ->whereNull('return_date')
            ->where('due_date', '<', now())
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get books due today.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBooksDueToday()
    {
        return LibraryIssue::with(['book', 'member'])
            ->whereNull('return_date')
            ->whereDate('due_date', now())
            ->get();
    }

    /**
     * Get issues with unpaid fines.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnpaidFines()
    {
        return LibraryIssue::with(['book', 'member'])
            ->where('fine_amount', '>', 0)
            ->where('fine_paid', false)
            ->get();
    }

    /**
     * Get library statistics.
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        $totalBooks = LibraryBook::where('is_active', true)->sum('quantity');
        $availableBooks = LibraryBook::where('is_active', true)->sum('available_quantity');
        $issuedBooks = $totalBooks - $availableBooks;
        $totalMembers = LibraryMember::where('is_active', true)->count();
        $overdueCount = LibraryIssue::whereNull('return_date')->where('due_date', '<', now())->count();
        $totalFinesPending = LibraryIssue::where('fine_amount', '>', 0)->where('fine_paid', false)->sum('fine_amount');
        
        return [
            'total_books' => $totalBooks,
            'available_books' => $availableBooks,
            'issued_books' => $issuedBooks,
            'total_members' => $totalMembers,
            'overdue_count' => $overdueCount,
            'total_fines_pending' => $totalFinesPending,
        ];
    }

    /**
     * Generate unique membership number.
     * 
     * @return string
     */
    private function generateMembershipNumber(): string
    {
        $year = date('Y');
        $lastMember = LibraryMember::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();
        
        $sequence = $lastMember ? ((int) substr($lastMember->membership_number, -4)) + 1 : 1;
        
        return 'LIB' . $year . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Upload book cover image.
     * 
     * Prompt 411: Implement Library Book Cover Upload
     * 
     * @param LibraryBook $book
     * @param UploadedFile $file
     * @return array Upload result with path and URL
     */
    public function uploadBookCover(LibraryBook $book, UploadedFile $file): array
    {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'book_cover');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Delete old cover if exists
        if ($book->cover_image) {
            $this->fileUploadService->delete($book->cover_image, 'public_uploads');
        }

        // Upload new cover
        $result = $this->fileUploadService->uploadBookCover($file, $book->id);

        // Update book record
        $book->update(['cover_image' => $result['path']]);

        return $result;
    }

    /**
     * Add book with cover image.
     * 
     * Prompt 411: Implement Library Book Cover Upload
     * 
     * @param array $data
     * @param UploadedFile|null $coverImage
     * @return LibraryBook
     */
    public function addBookWithCover(array $data, ?UploadedFile $coverImage = null): LibraryBook
    {
        return DB::transaction(function () use ($data, $coverImage) {
            // Create book first
            $book = $this->addBook($data);

            // Upload cover if provided
            if ($coverImage instanceof UploadedFile) {
                $this->uploadBookCover($book, $coverImage);
            }

            return $book->fresh();
        });
    }

    /**
     * Delete book cover image.
     * 
     * Prompt 411: Implement Library Book Cover Upload
     * 
     * @param LibraryBook $book
     * @return bool
     */
    public function deleteBookCover(LibraryBook $book): bool
    {
        if (!$book->cover_image) {
            return false;
        }

        // Delete file from storage
        $this->fileUploadService->delete($book->cover_image, 'public_uploads');

        // Update book record
        $book->update(['cover_image' => null]);

        return true;
    }

    /**
     * Replace book cover image.
     * 
     * Prompt 411: Implement Library Book Cover Upload
     * 
     * @param LibraryBook $book
     * @param UploadedFile $file
     * @return array Upload result with path and URL
     */
    public function replaceBookCover(LibraryBook $book, UploadedFile $file): array
    {
        return $this->uploadBookCover($book, $file);
    }
}
