<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SchoolClass Model (named SchoolClass to avoid PHP reserved word 'Class')
 * 
 * Prompt 75: Create Class Model with relationships to Section, Student,
 * ClassSubject, ClassTimetable, FeesMaster, ExamSchedule, Homework, StudyMaterial.
 * 
 * @property int $id
 * @property int $academic_session_id
 * @property string $name
 * @property string $display_name
 * @property int $section_count
 * @property int $order_index
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class SchoolClass extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'classes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'academic_session_id',
        'name',
        'display_name',
        'section_count',
        'order_index',
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
            'section_count' => 'integer',
            'order_index' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the academic session that owns this class.
     */
    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    /**
     * Get the sections for this class.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    /**
     * Get the students for this class.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    /**
     * Get the class subjects for this class.
     */
    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class, 'class_id');
    }

    /**
     * Get the class timetables for this class.
     */
    public function classTimetables(): HasMany
    {
        return $this->hasMany(ClassTimetable::class, 'class_id');
    }

    /**
     * Get the fees masters for this class.
     */
    public function feesMasters(): HasMany
    {
        return $this->hasMany(FeesMaster::class, 'class_id');
    }

    /**
     * Get the exam schedules for this class.
     */
    public function examSchedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class, 'class_id');
    }

    /**
     * Get the homework for this class.
     */
    public function homework(): HasMany
    {
        return $this->hasMany(Homework::class, 'class_id');
    }

    /**
     * Get the study materials for this class.
     */
    public function studyMaterials(): HasMany
    {
        return $this->hasMany(StudyMaterial::class, 'class_id');
    }

    /**
     * Get the attendances for this class.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    /**
     * Scope a query to only include active classes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by order_index.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }

    /**
     * Check if this class is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get the total number of students in this class.
     */
    public function getStudentCountAttribute(): int
    {
        return $this->students()->count();
    }
}
