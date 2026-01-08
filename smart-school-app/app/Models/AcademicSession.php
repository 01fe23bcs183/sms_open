<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * AcademicSession Model
 * 
 * Prompt 74: Create AcademicSession Model with relationships to Class, Student,
 * Exam, FeesMaster, TransportStudent, HostelAssignment.
 * 
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property bool $is_current
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class AcademicSession extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'academic_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
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
            'is_current' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the classes for this academic session.
     */
    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'academic_session_id');
    }

    /**
     * Get the students for this academic session.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'academic_session_id');
    }

    /**
     * Get the exams for this academic session.
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'academic_session_id');
    }

    /**
     * Get the fees masters for this academic session.
     */
    public function feesMasters(): HasMany
    {
        return $this->hasMany(FeesMaster::class, 'academic_session_id');
    }

    /**
     * Get the transport students for this academic session.
     */
    public function transportStudents(): HasMany
    {
        return $this->hasMany(TransportStudent::class, 'academic_session_id');
    }

    /**
     * Get the hostel assignments for this academic session.
     */
    public function hostelAssignments(): HasMany
    {
        return $this->hasMany(HostelAssignment::class, 'academic_session_id');
    }

    /**
     * Scope a query to only include active sessions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include the current session.
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Get the current academic session.
     */
    public static function getCurrentSession(): ?self
    {
        return static::current()->first();
    }

    /**
     * Check if this is the current session.
     */
    public function isCurrent(): bool
    {
        return $this->is_current;
    }

    /**
     * Check if this session is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Set this session as the current session.
     */
    public function setAsCurrent(): bool
    {
        static::query()->update(['is_current' => false]);
        return $this->update(['is_current' => true]);
    }
}
