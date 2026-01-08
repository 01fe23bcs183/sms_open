<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ExamSchedule Model
 * 
 * Prompt 81: Create ExamSchedule Model with relationships to Exam, Class, Section,
 * Subject, ExamAttendance, ExamMark.
 * 
 * @property int $id
 * @property int $exam_id
 * @property int $class_id
 * @property int|null $section_id
 * @property int $subject_id
 * @property \Carbon\Carbon $exam_date
 * @property string $start_time
 * @property string $end_time
 * @property string|null $room_number
 * @property float $full_marks
 * @property float $passing_marks
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ExamSchedule extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exam_schedules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'exam_id',
        'class_id',
        'section_id',
        'subject_id',
        'exam_date',
        'start_time',
        'end_time',
        'room_number',
        'full_marks',
        'passing_marks',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'full_marks' => 'decimal:2',
            'passing_marks' => 'decimal:2',
        ];
    }

    /**
     * Get the exam for this schedule.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    /**
     * Get the class for this schedule.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the section for this schedule.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * Get the subject for this schedule.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Get the exam attendances for this schedule.
     */
    public function examAttendances(): HasMany
    {
        return $this->hasMany(ExamAttendance::class, 'exam_schedule_id');
    }

    /**
     * Get the exam marks for this schedule.
     */
    public function examMarks(): HasMany
    {
        return $this->hasMany(ExamMark::class, 'exam_schedule_id');
    }

    /**
     * Scope a query to filter by exam.
     */
    public function scopeForExam($query, int $examId)
    {
        return $query->where('exam_id', $examId);
    }

    /**
     * Scope a query to filter by class.
     */
    public function scopeForClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope a query to filter by section.
     */
    public function scopeForSection($query, int $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    /**
     * Scope a query to filter by subject.
     */
    public function scopeForSubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope a query to filter by date.
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('exam_date', $date);
    }

    /**
     * Scope a query to filter upcoming schedules.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('exam_date', '>', now());
    }

    /**
     * Scope a query to filter past schedules.
     */
    public function scopePast($query)
    {
        return $query->where('exam_date', '<', now());
    }

    /**
     * Check if this schedule is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->exam_date->isFuture();
    }

    /**
     * Check if this schedule is today.
     */
    public function isToday(): bool
    {
        return $this->exam_date->isToday();
    }

    /**
     * Check if this schedule is past.
     */
    public function isPast(): bool
    {
        return $this->exam_date->isPast();
    }

    /**
     * Get the duration of the exam in minutes.
     */
    public function getDurationAttribute(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * Get the pass percentage for this exam.
     */
    public function getPassPercentageAttribute(): float
    {
        if ($this->full_marks == 0) {
            return 0;
        }
        return round(($this->passing_marks / $this->full_marks) * 100, 2);
    }

    /**
     * Get the display name for this schedule.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->subject->name} - {$this->exam_date->format('d M Y')}";
    }
}
