<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Payment Proof Model
 * 
 * Prompt 410: Create Payment Proof Model
 * 
 * Stores metadata for payment proof uploads including receipts,
 * bank transfer screenshots, and other payment verification documents.
 */
class PaymentProof extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fees_transaction_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'disk',
        'description',
        'uploaded_by',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the fees transaction that owns the proof.
     *
     * @return BelongsTo
     */
    public function feesTransaction(): BelongsTo
    {
        return $this->belongsTo(FeesTransaction::class);
    }

    /**
     * Get the user who uploaded the proof.
     *
     * @return BelongsTo
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the user who verified the proof.
     *
     * @return BelongsTo
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the full URL for the proof (if public).
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
     * Get the full path to the proof.
     *
     * @return string
     */
    public function getFullPathAttribute(): string
    {
        return Storage::disk($this->disk ?? 'private_uploads')->path($this->file_path);
    }

    /**
     * Check if the proof exists in storage.
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
     * Check if the proof is an image.
     *
     * @return bool
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if the proof is a PDF.
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
        return 'bi-file-earmark';
    }

    /**
     * Mark the proof as verified.
     *
     * @param int $userId
     * @return bool
     */
    public function markAsVerified(int $userId): bool
    {
        return $this->update([
            'is_verified' => true,
            'verified_by' => $userId,
            'verified_at' => now(),
        ]);
    }

    /**
     * Scope to get verified proofs.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to get unverified proofs.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Download the proof.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download()
    {
        return Storage::disk($this->disk ?? 'private_uploads')
            ->download($this->file_path, $this->original_name);
    }
}
