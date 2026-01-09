<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Student Document Model
 * 
 * Prompt 397: Create Student Document Model
 * 
 * Stores metadata for student documents including IDs, certificates,
 * and other uploaded files. Supports soft deletes for data preservation.
 */
class StudentDocument extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'document_type',
        'document_name',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'disk',
        'description',
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
     * Document type constants.
     */
    public const TYPE_BIRTH_CERTIFICATE = 'birth_certificate';
    public const TYPE_TRANSFER_CERTIFICATE = 'transfer_certificate';
    public const TYPE_AADHAR_CARD = 'aadhar_card';
    public const TYPE_PASSPORT = 'passport';
    public const TYPE_PHOTO = 'photo';
    public const TYPE_MARKSHEET = 'marksheet';
    public const TYPE_MEDICAL_CERTIFICATE = 'medical_certificate';
    public const TYPE_CASTE_CERTIFICATE = 'caste_certificate';
    public const TYPE_INCOME_CERTIFICATE = 'income_certificate';
    public const TYPE_OTHER = 'other';

    /**
     * Get all document types.
     *
     * @return array
     */
    public static function getDocumentTypes(): array
    {
        return [
            self::TYPE_BIRTH_CERTIFICATE => 'Birth Certificate',
            self::TYPE_TRANSFER_CERTIFICATE => 'Transfer Certificate',
            self::TYPE_AADHAR_CARD => 'Aadhar Card',
            self::TYPE_PASSPORT => 'Passport',
            self::TYPE_PHOTO => 'Photo',
            self::TYPE_MARKSHEET => 'Marksheet',
            self::TYPE_MEDICAL_CERTIFICATE => 'Medical Certificate',
            self::TYPE_CASTE_CERTIFICATE => 'Caste Certificate',
            self::TYPE_INCOME_CERTIFICATE => 'Income Certificate',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get the student that owns the document.
     *
     * @return BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
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
}
