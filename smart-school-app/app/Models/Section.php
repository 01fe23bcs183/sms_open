<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Section Model
 * 
 * Prompt 76: Create Section Model with relationships to Class, User (class_teacher),
 * Student, ClassSubject, ClassTimetable, Attendance, Homework, StudyMaterial.
 * 
 * @property int $id
 * @property int $class_id
 * @property string $name
 * @property string $display_name
 * @property int $capacity
 * @property string|null $room_number
 * @property int|null $class_teacher_id
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Section extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sections';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'class_id',
        'name',
        'display_name',
        'capacity',
        'room_number',
        'class_teacher_id',
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
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the class that owns this section.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the class teacher for this section.
     */
    public function classTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'class_teacher_id');
    }

    /**
     * Get the students for this section.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'section_id');
    }

    /**
     * Get the class subjects for this section.
     */
    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class, 'section_id');
    }

    /**
     * Get the class timetables for this section.
     */
    public function classTimetables(): HasMany
    {
        return $this->hasMany(ClassTimetable::class, 'section_id');
    }

    /**
     * Get the attendances for this section.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'section_id');
    }

    /**
     * Get the homework for this section.
     */
    public function homework(): HasMany
    {
        return $this->hasMany(Homework::class, 'section_id');
    }

    /**
     * Get the study materials for this section.
     */
    public function studyMaterials(): HasMany
    {
        return $this->hasMany(StudyMaterial::class, 'section_id');
    }

    /**
     * Get the fees masters for this section.
     */
    public function feesMasters(): HasMany
    {
        return $this->hasMany(FeesMaster::class, 'section_id');
    }

    /**
     * Get the exam schedules for this section.
     */
    public function examSchedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class, 'section_id');
    }

    /**
     * Scope a query to only include active sections.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if this section is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get the full name of the section (e.g., "Class 1 - Section A").
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->schoolClass->display_name} - {$this->display_name}";
    }

    /**
     * Get the total number of students in this section.
     */
    public function getStudentCountAttribute(): int
    {
        return $this->students()->count();
    }

    /**
     * Check if the section has reached capacity.
     */
    public function isFull(): bool
    {
        return $this->student_count >= $this->capacity;
    }

    /**
     * Get the available seats in this section.
     */
    public function getAvailableSeatsAttribute(): int
    {
        return max(0, $this->capacity - $this->student_count);
    }
}
