<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Teacher Document Model
 * 
 * Prompt 398: Create Teacher Document Model
 * 
 * Stores metadata for teacher/staff documents including IDs, certificates,
 * contracts, and other uploaded files. Supports soft deletes and expiry tracking.
 */
class TeacherDocument extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'document_type',
        'document_name',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'disk',
        'description',
        'expiry_date',
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
        'expiry_date' => 'date',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Document type constants.
     */
    public const TYPE_ID_PROOF = 'id_proof';
    public const TYPE_AADHAR_CARD = 'aadhar_card';
    public const TYPE_PAN_CARD = 'pan_card';
    public const TYPE_PASSPORT = 'passport';
    public const TYPE_DEGREE_CERTIFICATE = 'degree_certificate';
    public const TYPE_EXPERIENCE_CERTIFICATE = 'experience_certificate';
    public const TYPE_TEACHING_CERTIFICATE = 'teaching_certificate';
    public const TYPE_CONTRACT = 'contract';
    public const TYPE_RESUME = 'resume';
    public const TYPE_PHOTO = 'photo';
    public const TYPE_MEDICAL_CERTIFICATE = 'medical_certificate';
    public const TYPE_POLICE_VERIFICATION = 'police_verification';
    public const TYPE_OTHER = 'other';

    /**
     * Get all document types.
     *
     * @return array
     */
    public static function getDocumentTypes(): array
    {
        return [
            self::TYPE_ID_PROOF => 'ID Proof',
            self::TYPE_AADHAR_CARD => 'Aadhar Card',
            self::TYPE_PAN_CARD => 'PAN Card',
            self::TYPE_PASSPORT => 'Passport',
            self::TYPE_DEGREE_CERTIFICATE => 'Degree Certificate',
            self::TYPE_EXPERIENCE_CERTIFICATE => 'Experience Certificate',
            self::TYPE_TEACHING_CERTIFICATE => 'Teaching Certificate',
            self::TYPE_CONTRACT => 'Contract',
            self::TYPE_RESUME => 'Resume/CV',
            self::TYPE_PHOTO => 'Photo',
            self::TYPE_MEDICAL_CERTIFICATE => 'Medical Certificate',
            self::TYPE_POLICE_VERIFICATION => 'Police Verification',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get the teacher/user that owns the document.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias for user relationship.
     *
     * @return BelongsTo
     */
    public function teacher(): BelongsTo
    {
        return $this->user();
    }

    /**
     * Get the user who verified the document.
     *
     * @return BelongsTo
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the full URL for the document (if public).
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
     * Get the full path to the document.
     *
     * @return string
     */
    public function getFullPathAttribute(): string
    {
        return Storage::disk($this->disk ?? 'private_uploads')->path($this->file_path);
    }

    /**
     * Check if the document exists in storage.
     *
     * @return bool
     */
    public function existsInStorage(): bool
    {
        return Storage::disk($this->disk ?? 'private_uploads')->exists($this->file_path);
    }

    /**
     * Get the document type label.
     *
     * @return string
     */
    public function getDocumentTypeLabelAttribute(): string
    {
        $types = self::getDocumentTypes();
        return $types[$this->document_type] ?? ucfirst(str_replace('_', ' ', $this->document_type));
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
     * Check if the document is expired.
     *
     * @return bool
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    /**
     * Check if the document is expiring soon (within 30 days).
     *
     * @param int $days
     * @return bool
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isBetween(now(), now()->addDays($days));
    }

    /**
     * Get days until expiry.
     *
     * @return int|null
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Mark the document as verified.
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
     * Scope to get verified documents.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to get unverified documents.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Scope to filter by document type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope to get expired documents.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now());
    }

    /**
     * Scope to get documents expiring soon.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays($days));
    }
}
