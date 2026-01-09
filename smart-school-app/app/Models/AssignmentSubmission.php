<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Assignment Submission Model
 * 
 * Prompt 404: Create Assignment Submission Model
 * 
 * Stores student assignment submissions including uploaded files,
 * submission timestamps, grades, and feedback.
 */
class AssignmentSubmission extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'homework_id',
        'student_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'disk',
        'remarks',
        'submitted_at',
        'is_late',
        'resubmission_count',
        'marks',
        'feedback',
        'graded_by',
        'graded_at',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'is_late' => 'boolean',
        'resubmission_count' => 'integer',
        'marks' => 'float',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
    ];

    /**
     * Status constants.
     */
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_GRADED = 'graded';
    public const STATUS_RETURNED = 'returned';

    /**
     * Get all status options.
     *
     * @return array
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_GRADED => 'Graded',
            self::STATUS_RETURNED => 'Returned',
        ];
    }

    /**
     * Get the homework that this submission belongs to.
     *
     * @return BelongsTo
     */
    public function homework(): BelongsTo
    {
        return $this->belongsTo(Homework::class);
    }

    /**
     * Get the student who submitted.
     *
     * @return BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the teacher who graded.
     *
     * @return BelongsTo
     */
    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Get the full URL for the submission file (if public).
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
     * Get the full path to the submission file.
     *
     * @return string
     */
    public function getFullPathAttribute(): string
    {
        return Storage::disk($this->disk ?? 'private_uploads')->path($this->file_path);
    }

    /**
     * Check if the submission file exists in storage.
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
     * Check if the submission is graded.
     *
     * @return bool
     */
    public function getIsGradedAttribute(): bool
    {
        return $this->graded_at !== null;
    }

    /**
     * Check if the submission is pending grading.
     *
     * @return bool
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->graded_at === null;
    }

    /**
     * Get the status label.
     *
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        $statuses = self::getStatusOptions();
        return $statuses[$this->status] ?? ucfirst($this->status ?? 'submitted');
    }

    /**
     * Get the status badge class.
     *
     * @return string
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_GRADED => 'bg-success',
            self::STATUS_RETURNED => 'bg-info',
            default => $this->is_late ? 'bg-warning' : 'bg-primary',
        };
    }

    /**
     * Check if the submission is an image.
     *
     * @return bool
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if the submission is a PDF.
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
     * Download the submission file.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download()
    {
        return Storage::disk($this->disk ?? 'private_uploads')
            ->download($this->file_path, $this->original_name);
    }

    /**
     * Scope to get late submissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    /**
     * Scope to get on-time submissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnTime($query)
    {
        return $query->where('is_late', false);
    }

    /**
     * Scope to get graded submissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGraded($query)
    {
        return $query->whereNotNull('graded_at');
    }

    /**
     * Scope to get pending submissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->whereNull('graded_at');
    }

    /**
     * Scope to filter by homework.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $homeworkId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForHomework($query, int $homeworkId)
    {
        return $query->where('homework_id', $homeworkId);
    }

    /**
     * Scope to filter by student.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $studentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}
