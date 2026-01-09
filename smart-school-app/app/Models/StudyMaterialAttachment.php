<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Study Material Attachment Model
 * 
 * Prompt 403: Create Study Material Attachment Model
 * 
 * Stores metadata for study material attachments including PDFs,
 * videos, presentations, and other educational resources.
 */
class StudyMaterialAttachment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'study_material_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'disk',
        'description',
        'download_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'download_count' => 'integer',
    ];

    /**
     * Get the study material that owns the attachment.
     *
     * @return BelongsTo
     */
    public function studyMaterial(): BelongsTo
    {
        return $this->belongsTo(StudyMaterial::class);
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
     * Check if the attachment is a video.
     *
     * @return bool
     */
    public function getIsVideoAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
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
     * Check if the attachment is a document.
     *
     * @return bool
     */
    public function getIsDocumentAttribute(): bool
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
        ];
        return in_array($this->mime_type, $documentTypes);
    }

    /**
     * Get the file icon based on mime type.
     *
     * @return string
     */
    public function getIconAttribute(): string
    {
        if ($this->is_video) {
            return 'bi-file-play';
        }
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
        if (str_contains($this->mime_type, 'powerpoint') || str_contains($this->mime_type, 'presentation')) {
            return 'bi-file-ppt';
        }
        return 'bi-file-earmark';
    }

    /**
     * Increment download count.
     *
     * @return bool
     */
    public function incrementDownloadCount(): bool
    {
        return $this->increment('download_count');
    }

    /**
     * Download the attachment.
     *
     * @param bool $trackDownload
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(bool $trackDownload = true)
    {
        if ($trackDownload) {
            $this->incrementDownloadCount();
        }
        
        return Storage::disk($this->disk ?? 'private_uploads')
            ->download($this->file_path, $this->original_name);
    }
}
