<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Exam Model
 * 
 * Prompt 80: Create Exam Model with relationships to AcademicSession, ExamType,
 * ExamSchedule, ExamAttendance, ExamMark.
 * 
 * @property int $id
 * @property int $academic_session_id
 * @property int $exam_type_id
 * @property string $name
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Exam extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exams';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'academic_session_id',
        'exam_type_id',
        'name',
        'start_date',
        'end_date',
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
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the academic session for this exam.
     */
    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    /**
     * Get the exam type for this exam.
     */
    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class, 'exam_type_id');
    }

    /**
     * Get the exam schedules for this exam.
     */
    public function examSchedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class, 'exam_id');
    }

    /**
     * Get the exam marks through exam schedules.
     */
    public function examMarks(): HasManyThrough
    {
        return $this->hasManyThrough(
            ExamMark::class,
            ExamSchedule::class,
            'exam_id',
            'exam_schedule_id',
            'id',
            'id'
        );
    }

    /**
     * Get the exam attendances through exam schedules.
     */
    public function examAttendances(): HasManyThrough
    {
        return $this->hasManyThrough(
            ExamAttendance::class,
            ExamSchedule::class,
            'exam_id',
            'exam_schedule_id',
            'id',
            'id'
        );
    }

    /**
     * Scope a query to only include active exams.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by academic session.
     */
    public function scopeInSession($query, int $sessionId)
    {
        return $query->where('academic_session_id', $sessionId);
    }

    /**
     * Scope a query to filter by exam type.
     */
    public function scopeOfType($query, int $typeId)
    {
        return $query->where('exam_type_id', $typeId);
    }

    /**
     * Scope a query to filter upcoming exams.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Scope a query to filter ongoing exams.
     */
    public function scopeOngoing($query)
    {
        return $query->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }

    /**
     * Scope a query to filter completed exams.
     */
    public function scopeCompleted($query)
    {
        return $query->where('end_date', '<', now());
    }

    /**
     * Check if this exam is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if this exam is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_date->isFuture();
    }

    /**
     * Check if this exam is ongoing.
     */
    public function isOngoing(): bool
    {
        return $this->start_date->isPast() && $this->end_date->isFuture();
    }

    /**
     * Check if this exam is completed.
     */
    public function isCompleted(): bool
    {
        return $this->end_date->isPast();
    }

    /**
     * Get the duration of the exam in days.
     */
    public function getDurationAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Get the status of the exam.
     */
    public function getStatusAttribute(): string
    {
        if ($this->isUpcoming()) {
            return 'upcoming';
        }
        if ($this->isOngoing()) {
            return 'ongoing';
        }
        return 'completed';
    }
}
