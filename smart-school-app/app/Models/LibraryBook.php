<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryBook extends Model
{
    use HasFactory, SoftDeletes;

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

    public function category(): BelongsTo
    {
        return $this->belongsTo(LibraryCategory::class, 'category_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(LibraryIssue::class, 'book_id');
    }
}
