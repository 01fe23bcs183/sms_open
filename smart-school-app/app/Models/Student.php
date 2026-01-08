<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Student Model
 * 
 * Prompt 78: Create Student Model with comprehensive relationships to User,
 * AcademicSession, Class, Section, StudentCategory, StudentSibling, StudentDocument,
 * StudentPromotion, Attendance, ExamMark, FeesAllotment, FeesTransaction,
 * TransportStudent, HostelAssignment.
 * 
 * @property int $id
 * @property int $user_id
 * @property int $academic_session_id
 * @property string $admission_number
 * @property string|null $roll_number
 * @property int $class_id
 * @property int $section_id
 * @property \Carbon\Carbon $date_of_admission
 * @property \Carbon\Carbon $date_of_birth
 * @property string $gender
 * @property string|null $blood_group
 * @property string|null $religion
 * @property string|null $caste
 * @property string $nationality
 * @property string|null $mother_tongue
 * @property string|null $father_name
 * @property string|null $father_phone
 * @property string|null $father_occupation
 * @property string|null $father_email
 * @property string|null $father_qualification
 * @property float|null $father_annual_income
 * @property string|null $mother_name
 * @property string|null $mother_phone
 * @property string|null $mother_occupation
 * @property string|null $mother_email
 * @property string|null $mother_qualification
 * @property float|null $mother_annual_income
 * @property string|null $guardian_name
 * @property string|null $guardian_phone
 * @property string|null $guardian_relation
 * @property string|null $guardian_occupation
 * @property string|null $guardian_email
 * @property string|null $guardian_address
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string $country
 * @property string|null $postal_code
 * @property string|null $permanent_address
 * @property string|null $permanent_city
 * @property string|null $permanent_state
 * @property string $permanent_country
 * @property string|null $permanent_postal_code
 * @property string|null $previous_school_name
 * @property string|null $previous_school_address
 * @property string|null $previous_class
 * @property string|null $transfer_certificate_number
 * @property \Carbon\Carbon|null $transfer_certificate_date
 * @property bool $is_rte
 * @property string $admission_type
 * @property int|null $category_id
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_phone
 * @property string|null $emergency_contact_relation
 * @property string|null $medical_notes
 * @property string|null $allergies
 * @property float|null $height
 * @property float|null $weight
 * @property string|null $identification_marks
 * @property string|null $bank_name
 * @property string|null $bank_account_number
 * @property string|null $bank_ifsc_code
 * @property string|null $photo
 * @property string|null $birth_certificate
 * @property string|null $aadhar_number
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Student extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'students';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'academic_session_id',
        'admission_number',
        'roll_number',
        'class_id',
        'section_id',
        'date_of_admission',
        'date_of_birth',
        'gender',
        'blood_group',
        'religion',
        'caste',
        'nationality',
        'mother_tongue',
        'father_name',
        'father_phone',
        'father_occupation',
        'father_email',
        'father_qualification',
        'father_annual_income',
        'mother_name',
        'mother_phone',
        'mother_occupation',
        'mother_email',
        'mother_qualification',
        'mother_annual_income',
        'guardian_name',
        'guardian_phone',
        'guardian_relation',
        'guardian_occupation',
        'guardian_email',
        'guardian_address',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'permanent_address',
        'permanent_city',
        'permanent_state',
        'permanent_country',
        'permanent_postal_code',
        'previous_school_name',
        'previous_school_address',
        'previous_class',
        'transfer_certificate_number',
        'transfer_certificate_date',
        'is_rte',
        'admission_type',
        'category_id',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'medical_notes',
        'allergies',
        'height',
        'weight',
        'identification_marks',
        'bank_name',
        'bank_account_number',
        'bank_ifsc_code',
        'photo',
        'birth_certificate',
        'aadhar_number',
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
            'date_of_admission' => 'date',
            'date_of_birth' => 'date',
            'transfer_certificate_date' => 'date',
            'father_annual_income' => 'decimal:2',
            'mother_annual_income' => 'decimal:2',
            'height' => 'decimal:2',
            'weight' => 'decimal:2',
            'is_rte' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this student.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the academic session for this student.
     */
    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    /**
     * Get the class for this student.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the section for this student.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * Get the category for this student.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(StudentCategory::class, 'category_id');
    }

    /**
     * Get the siblings for this student.
     */
    public function siblings(): HasMany
    {
        return $this->hasMany(StudentSibling::class, 'student_id');
    }

    /**
     * Get the documents for this student.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class, 'student_id');
    }

    /**
     * Get the promotions for this student.
     */
    public function promotions(): HasMany
    {
        return $this->hasMany(StudentPromotion::class, 'student_id');
    }

    /**
     * Get the attendances for this student.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    /**
     * Get the exam marks for this student.
     */
    public function examMarks(): HasMany
    {
        return $this->hasMany(ExamMark::class, 'student_id');
    }

    /**
     * Get the fees allotments for this student.
     */
    public function feesAllotments(): HasMany
    {
        return $this->hasMany(FeesAllotment::class, 'student_id');
    }

    /**
     * Get the fees transactions for this student.
     */
    public function feesTransactions(): HasMany
    {
        return $this->hasMany(FeesTransaction::class, 'student_id');
    }

    /**
     * Get the transport assignment for this student.
     */
    public function transportStudent(): HasOne
    {
        return $this->hasOne(TransportStudent::class, 'student_id');
    }

    /**
     * Get the hostel assignment for this student.
     */
    public function hostelAssignment(): HasOne
    {
        return $this->hasOne(HostelAssignment::class, 'student_id');
    }

    /**
     * Scope a query to only include active students.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
     * Scope a query to filter by academic session.
     */
    public function scopeInSession($query, int $sessionId)
    {
        return $query->where('academic_session_id', $sessionId);
    }

    /**
     * Check if this student is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if this student is RTE (Right to Education).
     */
    public function isRte(): bool
    {
        return $this->is_rte;
    }

    /**
     * Get the student's full name from the user.
     */
    public function getFullNameAttribute(): string
    {
        return $this->user->full_name ?? '';
    }

    /**
     * Get the student's age.
     */
    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->age;
    }

    /**
     * Get the class and section display name.
     */
    public function getClassSectionAttribute(): string
    {
        return "{$this->schoolClass->display_name} - {$this->section->display_name}";
    }
}
