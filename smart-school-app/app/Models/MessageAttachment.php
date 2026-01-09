<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Message Attachment Model
 * 
 * Prompt 409: Create Message Attachment Model
 * 
 * Stores metadata for message attachments including documents,
 * images, and other files sent with internal messages.
 */
class MessageAttachment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'message_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'disk',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * Get the message that owns the attachment.
     *
     * @return BelongsTo
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the full URL for the attachment (if public).
     *
     * @return string|null
     */
    public function getUrlAttribute(): ?string
    {
        if ($this->disk === 'public_uploads' || $this->disk === 'public') {
            return Storage::disk($this->disk)->url($this->file_path);
        }
        return null;
    }

    /**
     * Get the full path to the attachment.
     *
     * @return string
     */
    public function getFullPathAttribute(): string
    {
        return Storage::disk($this->disk ?? 'private_uploads')->path($this->file_path);
    }

    /**
     * Check if the attachment exists in storage.
     *
     * @return bool
     */
    public function existsInStorage(): bool
    {
        return Storage::disk($this->disk ?? 'private_uploads')->exists($this->file_path);
    }

    /**
     * Get the file extension.
     *
     * @return string
     */
    public function getExtensionAttribute(): string
    {
        return $this->original_name ? pathinfo($this->original_name, PATHINFO_EXTENSION) : '';
    }

    /**
     * Get human-readable file size.
     *
     * @return string
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if the attachment is an image.
     *
     * @return bool
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if the attachment is a PDF.
     *
     * @return bool
     */
    public function getIsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Get the file icon based on mime type.
     *
     * @return string
     */
    public function getIconAttribute(): string
    {
        if ($this->is_image) {
            return 'bi-file-image';
        }
        if ($this->is_pdf) {
            return 'bi-file-pdf';
        }
        if (str_contains($this->mime_type, 'word')) {
            return 'bi-file-word';
        }
        if (str_contains($this->mime_type, 'excel') || str_contains($this->mime_type, 'spreadsheet')) {
            return 'bi-file-excel';
        }
        return 'bi-file-earmark';
    }

    /**
     * Download the attachment.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download()
    {
        return Storage::disk($this->disk ?? 'private_uploads')
            ->download($this->file_path, $this->original_name);
    }
}
