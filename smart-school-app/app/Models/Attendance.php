<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attendance Model
 * 
 * Prompt 79: Create Attendance Model with relationships to Student, Class,
 * Section, AttendanceType, User (marked_by).
 * 
 * @property int $id
 * @property int $student_id
 * @property int $class_id
 * @property int $section_id
 * @property \Carbon\Carbon $attendance_date
 * @property int $attendance_type_id
 * @property string|null $remarks
 * @property int|null $marked_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Attendance extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attendances';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'class_id',
        'section_id',
        'attendance_date',
        'attendance_type_id',
        'remarks',
        'marked_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
        ];
    }

    /**
     * Get the student for this attendance.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the class for this attendance.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the section for this attendance.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * Get the attendance type for this attendance.
     */
    public function attendanceType(): BelongsTo
    {
        return $this->belongsTo(AttendanceType::class, 'attendance_type_id');
    }

    /**
     * Get the user who marked this attendance.
     */
    public function markedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    /**
     * Scope a query to filter by date.
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('attendance_date', $date);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by class.
     */
    public function scopeInClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope a query to filter by section.
     */
    public function scopeInSection($query, int $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    /**
     * Scope a query to filter by student.
     */
    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query to filter by attendance type.
     */
    public function scopeOfType($query, int $typeId)
    {
        return $query->where('attendance_type_id', $typeId);
    }

    /**
     * Check if the student was present.
     */
    public function isPresent(): bool
    {
        return $this->attendanceType->name === 'present';
    }

    /**
     * Check if the student was absent.
     */
    public function isAbsent(): bool
    {
        return $this->attendanceType->name === 'absent';
    }

    /**
     * Check if the student was late.
     */
    public function isLate(): bool
    {
        return $this->attendanceType->name === 'late';
    }

    /**
     * Check if the student was on leave.
     */
    public function isOnLeave(): bool
    {
        return $this->attendanceType->name === 'leave';
    }
}
