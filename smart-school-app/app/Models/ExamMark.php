<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ExamMark Model
 * 
 * Prompt 82: Create ExamMark Model with relationships to ExamSchedule, Student,
 * ExamGrade, User (entered_by).
 * 
 * @property int $id
 * @property int $exam_schedule_id
 * @property int $student_id
 * @property float $obtained_marks
 * @property int|null $grade_id
 * @property string|null $remarks
 * @property int|null $entered_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ExamMark extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exam_marks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'exam_schedule_id',
        'student_id',
        'obtained_marks',
        'grade_id',
        'remarks',
        'entered_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'obtained_marks' => 'decimal:2',
        ];
    }

    /**
     * Get the exam schedule for this mark.
     */
    public function examSchedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
    }

    /**
     * Get the student for this mark.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the grade for this mark.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(ExamGrade::class, 'grade_id');
    }

    /**
     * Get the user who entered this mark.
     */
    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    /**
     * Scope a query to filter by exam schedule.
     */
    public function scopeForSchedule($query, int $scheduleId)
    {
        return $query->where('exam_schedule_id', $scheduleId);
    }

    /**
     * Scope a query to filter by student.
     */
    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query to filter by grade.
     */
    public function scopeWithGrade($query, int $gradeId)
    {
        return $query->where('grade_id', $gradeId);
    }

    /**
     * Scope a query to filter passing marks.
     */
    public function scopePassed($query)
    {
        return $query->whereHas('examSchedule', function ($q) {
            $q->whereColumn('exam_marks.obtained_marks', '>=', 'exam_schedules.passing_marks');
        });
    }

    /**
     * Scope a query to filter failing marks.
     */
    public function scopeFailed($query)
    {
        return $query->whereHas('examSchedule', function ($q) {
            $q->whereColumn('exam_marks.obtained_marks', '<', 'exam_schedules.passing_marks');
        });
    }

    /**
     * Check if this mark is passing.
     */
    public function isPassing(): bool
    {
        return $this->obtained_marks >= $this->examSchedule->passing_marks;
    }

    /**
     * Check if this mark is failing.
     */
    public function isFailing(): bool
    {
        return !$this->isPassing();
    }

    /**
     * Get the percentage for this mark.
     */
    public function getPercentageAttribute(): float
    {
        $fullMarks = $this->examSchedule->full_marks;
        if ($fullMarks == 0) {
            return 0;
        }
        return round(($this->obtained_marks / $fullMarks) * 100, 2);
    }

    /**
     * Get the marks needed to pass.
     */
    public function getMarksToPassAttribute(): float
    {
        if ($this->isPassing()) {
            return 0;
        }
        return $this->examSchedule->passing_marks - $this->obtained_marks;
    }

    /**
     * Get the result status.
     */
    public function getResultAttribute(): string
    {
        return $this->isPassing() ? 'Pass' : 'Fail';
    }
}
