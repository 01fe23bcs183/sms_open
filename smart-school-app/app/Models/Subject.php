<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Subject Model
 * 
 * Prompt 77: Create Subject Model with relationships to ClassSubject,
 * ClassTimetable, ExamSchedule, Homework, StudyMaterial.
 * 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $type
 * @property string|null $description
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Subject extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subjects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the class subjects for this subject.
     */
    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class, 'subject_id');
    }

    /**
     * Get the class timetables for this subject.
     */
    public function classTimetables(): HasMany
    {
        return $this->hasMany(ClassTimetable::class, 'subject_id');
    }

    /**
     * Get the exam schedules for this subject.
     */
    public function examSchedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class, 'subject_id');
    }

    /**
     * Get the homework for this subject.
     */
    public function homework(): HasMany
    {
        return $this->hasMany(Homework::class, 'subject_id');
    }

    /**
     * Get the study materials for this subject.
     */
    public function studyMaterials(): HasMany
    {
        return $this->hasMany(StudyMaterial::class, 'subject_id');
    }

    /**
     * Scope a query to only include active subjects.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include theory subjects.
     */
    public function scopeTheory($query)
    {
        return $query->where('type', 'theory');
    }

    /**
     * Scope a query to only include practical subjects.
     */
    public function scopePractical($query)
    {
        return $query->where('type', 'practical');
    }

    /**
     * Check if this subject is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if this is a theory subject.
     */
    public function isTheory(): bool
    {
        return $this->type === 'theory';
    }

    /**
     * Check if this is a practical subject.
     */
    public function isPractical(): bool
    {
        return $this->type === 'practical';
    }
}
