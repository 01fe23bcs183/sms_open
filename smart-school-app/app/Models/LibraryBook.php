<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * LibraryBook Model
 * 
 * Prompt 85: Create LibraryBook Model with relationships to LibraryCategory,
 * LibraryIssue.
 * 
 * @property int $id
 * @property int $category_id
 * @property string $isbn
 * @property string $title
 * @property string|null $author
 * @property string|null $publisher
 * @property string|null $edition
 * @property int|null $publish_year
 * @property string|null $rack_number
 * @property int $quantity
 * @property int $available_quantity
 * @property float|null $price
 * @property string|null $language
 * @property int|null $pages
 * @property string|null $description
 * @property string|null $cover_image
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class LibraryBook extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'library_books';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'isbn',
        'title',
        'author',
        'publisher',
        'edition',
        'publish_year',
        'rack_number',
        'quantity',
        'available_quantity',
        'price',
        'language',
        'pages',
        'description',
        'cover_image',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'publish_year' => 'integer',
            'quantity' => 'integer',
            'available_quantity' => 'integer',
            'price' => 'decimal:2',
            'pages' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the category for this book.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(LibraryCategory::class, 'category_id');
    }

    /**
     * Get the issues for this book.
     */
    public function issues(): HasMany
    {
        return $this->hasMany(LibraryIssue::class, 'book_id');
    }

    /**
     * Get the active issues for this book.
     */
    public function activeIssues(): HasMany
    {
        return $this->hasMany(LibraryIssue::class, 'book_id')
                    ->whereNull('return_date');
    }

    /**
     * Scope a query to only include active books.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeInCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to filter available books.
     */
    public function scopeAvailable($query)
    {
        return $query->where('available_quantity', '>', 0);
    }

    /**
     * Scope a query to filter unavailable books.
     */
    public function scopeUnavailable($query)
    {
        return $query->where('available_quantity', '<=', 0);
    }

    /**
     * Scope a query to search books by title or author.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('author', 'like', "%{$search}%")
              ->orWhere('isbn', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by language.
     */
    public function scopeInLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Check if this book is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if this book is available for issue.
     */
    public function isAvailable(): bool
    {
        return $this->available_quantity > 0;
    }

    /**
     * Get the number of issued copies.
     */
    public function getIssuedQuantityAttribute(): int
    {
        return $this->quantity - $this->available_quantity;
    }

    /**
     * Get the availability status.
     */
    public function getAvailabilityStatusAttribute(): string
    {
        if ($this->available_quantity == 0) {
            return 'Not Available';
        }
        if ($this->available_quantity < 3) {
            return 'Low Stock';
        }
        return 'Available';
    }

    /**
     * Issue a copy of this book.
     */
    public function issue(): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }
        $this->decrement('available_quantity');
        return true;
    }

    /**
     * Return a copy of this book.
     */
    public function return(): bool
    {
        if ($this->available_quantity >= $this->quantity) {
            return false;
        }
        $this->increment('available_quantity');
        return true;
    }

    /**
     * Get the full title with author.
     */
    public function getFullTitleAttribute(): string
    {
        if ($this->author) {
            return "{$this->title} by {$this->author}";
        }
        return $this->title;
    }
}
