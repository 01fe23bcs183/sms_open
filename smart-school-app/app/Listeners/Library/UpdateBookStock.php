<?php

namespace App\Listeners\Library;

use App\Events\LibraryBookIssued;
use App\Events\LibraryBookReturned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Update Book Stock Listener
 * 
 * Prompt 472: Create Library Event Listeners
 * 
 * Updates the available stock count when books are issued or returned.
 * Handles both LibraryBookIssued and LibraryBookReturned events.
 */
class UpdateBookStock implements ShouldQueue
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
    public function handle(LibraryBookIssued|LibraryBookReturned $event): void
    {
        try {
            $book = $event->getBook();
            $isIssue = $event instanceof LibraryBookIssued;

            if ($isIssue) {
                DB::table('library_books')
                    ->where('id', $book->id)
                    ->decrement('available_quantity');
            } else {
                DB::table('library_books')
                    ->where('id', $book->id)
                    ->increment('available_quantity');
            }

            $cacheKeys = [
                "library.book.{$book->id}",
                "library.book.{$book->id}.stock",
                "library.category.{$book->category_id}.books",
                "library.inventory.summary",
                "dashboard.library.summary",
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            Cache::tags(['library', 'books'])->flush();

            Log::info('Book stock updated', [
                'book_id' => $book->id,
                'action' => $isIssue ? 'issued' : 'returned',
                'book_title' => $book->title,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update book stock', [
                'book_id' => $event->book->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(LibraryBookIssued|LibraryBookReturned $event, \Throwable $exception): void
    {
        Log::error('Book stock update job failed', [
            'book_id' => $event->book->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
