<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Models\StudentDocument;
use App\Models\StudentPromotion;
use App\Models\AcademicSession;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

/**
 * Student Service
 * 
 * Prompt 323: Create Student Service
 * Prompt 393: Implement Student Photo Upload
 * Prompt 394: Implement Student Document Uploads
 * 
 * Centralizes student business logic including admission, profile updates,
 * promotions, and status changes. Handles photo/document uploads using
 * FileUploadService and wraps operations in transactions for safety.
 */
class StudentService
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Create a new student with user account.
     * 
     * @param array $data Student data
     * @return Student
     * @throws \Exception
     */
    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            // Create user account for student
            $user = $this->createUserAccount($data);
            
            // Generate admission number if not provided
            if (empty($data['admission_number'])) {
                $data['admission_number'] = $this->generateAdmissionNumber();
            }
            
            // Handle photo upload
            if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
                $data['photo'] = $this->uploadPhoto($data['photo']);
            }
            
            // Create student record
            $student = Student::create([
                'user_id' => $user->id,
                'academic_session_id' => $data['academic_session_id'],
                'admission_number' => $data['admission_number'],
                'roll_number' => $data['roll_number'] ?? null,
                'class_id' => $data['class_id'],
                'section_id' => $data['section_id'],
                'date_of_admission' => $data['date_of_admission'] ?? now(),
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'blood_group' => $data['blood_group'] ?? null,
                'religion' => $data['religion'] ?? null,
                'caste' => $data['caste'] ?? null,
                'nationality' => $data['nationality'] ?? 'Indian',
                'mother_tongue' => $data['mother_tongue'] ?? null,
                'father_name' => $data['father_name'] ?? null,
                'father_phone' => $data['father_phone'] ?? null,
                'father_occupation' => $data['father_occupation'] ?? null,
                'father_email' => $data['father_email'] ?? null,
                'father_qualification' => $data['father_qualification'] ?? null,
                'father_annual_income' => $data['father_annual_income'] ?? null,
                'mother_name' => $data['mother_name'] ?? null,
                'mother_phone' => $data['mother_phone'] ?? null,
                'mother_occupation' => $data['mother_occupation'] ?? null,
                'mother_email' => $data['mother_email'] ?? null,
                'mother_qualification' => $data['mother_qualification'] ?? null,
                'mother_annual_income' => $data['mother_annual_income'] ?? null,
                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_phone' => $data['guardian_phone'] ?? null,
                'guardian_relation' => $data['guardian_relation'] ?? null,
                'guardian_occupation' => $data['guardian_occupation'] ?? null,
                'guardian_email' => $data['guardian_email'] ?? null,
                'guardian_address' => $data['guardian_address'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? 'India',
                'postal_code' => $data['postal_code'] ?? null,
                'permanent_address' => $data['permanent_address'] ?? null,
                'permanent_city' => $data['permanent_city'] ?? null,
                'permanent_state' => $data['permanent_state'] ?? null,
                'permanent_country' => $data['permanent_country'] ?? 'India',
                'permanent_postal_code' => $data['permanent_postal_code'] ?? null,
                'previous_school_name' => $data['previous_school_name'] ?? null,
                'previous_school_address' => $data['previous_school_address'] ?? null,
                'previous_class' => $data['previous_class'] ?? null,
                'transfer_certificate_number' => $data['transfer_certificate_number'] ?? null,
                'transfer_certificate_date' => $data['transfer_certificate_date'] ?? null,
                'is_rte' => $data['is_rte'] ?? false,
                'admission_type' => $data['admission_type'] ?? 'new',
                'category_id' => $data['category_id'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'emergency_contact_relation' => $data['emergency_contact_relation'] ?? null,
                'medical_notes' => $data['medical_notes'] ?? null,
                'allergies' => $data['allergies'] ?? null,
                'height' => $data['height'] ?? null,
                'weight' => $data['weight'] ?? null,
                'identification_marks' => $data['identification_marks'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_number' => $data['bank_account_number'] ?? null,
                'bank_ifsc_code' => $data['bank_ifsc_code'] ?? null,
                'photo' => $data['photo'] ?? null,
                'aadhar_number' => $data['aadhar_number'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
            
            // Handle document uploads
            if (isset($data['documents']) && is_array($data['documents'])) {
                $this->uploadDocuments($student, $data['documents']);
            }
            
            return $student->load(['user', 'schoolClass', 'section', 'academicSession']);
        });
    }

    /**
     * Update student profile.
     * 
     * @param Student $student
     * @param array $data
     * @return Student
     */
    public function update(Student $student, array $data): Student
    {
        return DB::transaction(function () use ($student, $data) {
            // Update user account if name/email changed
            if (isset($data['first_name']) || isset($data['last_name']) || isset($data['email'])) {
                $student->user->update([
                    'first_name' => $data['first_name'] ?? $student->user->first_name,
                    'last_name' => $data['last_name'] ?? $student->user->last_name,
                    'name' => trim(($data['first_name'] ?? $student->user->first_name) . ' ' . ($data['last_name'] ?? $student->user->last_name)),
                    'email' => $data['email'] ?? $student->user->email,
                    'phone' => $data['phone'] ?? $student->user->phone,
                ]);
            }
            
            // Handle photo upload
            if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
                // Delete old photo
                if ($student->photo) {
                    Storage::disk('public')->delete($student->photo);
                }
                $data['photo'] = $this->uploadPhoto($data['photo']);
            }
            
            // Update student record
            $student->update($data);
            
            return $student->fresh(['user', 'schoolClass', 'section', 'academicSession']);
        });
    }

    /**
     * Promote student to next class/section.
     * 
     * @param Student $student
     * @param int $toSessionId
     * @param int $toClassId
     * @param int $toSectionId
     * @param string $status
     * @param string|null $remarks
     * @return StudentPromotion
     */
    public function promote(
        Student $student,
        int $toSessionId,
        int $toClassId,
        int $toSectionId,
        string $status = 'promoted',
        ?string $remarks = null
    ): StudentPromotion {
        return DB::transaction(function () use ($student, $toSessionId, $toClassId, $toSectionId, $status, $remarks) {
            // Create promotion record
            $promotion = StudentPromotion::create([
                'student_id' => $student->id,
                'from_session_id' => $student->academic_session_id,
                'from_class_id' => $student->class_id,
                'from_section_id' => $student->section_id,
                'to_session_id' => $toSessionId,
                'to_class_id' => $toClassId,
                'to_section_id' => $toSectionId,
                'status' => $status,
                'remarks' => $remarks,
                'promoted_at' => now(),
            ]);
            
            // Update student's current class/section
            $student->update([
                'academic_session_id' => $toSessionId,
                'class_id' => $toClassId,
                'section_id' => $toSectionId,
            ]);
            
            return $promotion;
        });
    }

    /**
     * Archive/deactivate a student.
     * 
     * @param Student $student
     * @param string|null $reason
     * @return Student
     */
    public function archive(Student $student, ?string $reason = null): Student
    {
        return DB::transaction(function () use ($student, $reason) {
            $student->update([
                'is_active' => false,
            ]);
            
            // Deactivate user account
            $student->user->update([
                'is_active' => false,
            ]);
            
            return $student;
        });
    }

    /**
     * Reactivate an archived student.
     * 
     * @param Student $student
     * @return Student
     */
    public function reactivate(Student $student): Student
    {
        return DB::transaction(function () use ($student) {
            $student->update([
                'is_active' => true,
            ]);
            
            $student->user->update([
                'is_active' => true,
            ]);
            
            return $student;
        });
    }

    /**
     * Get students by class and section.
     * 
     * @param int $classId
     * @param int|null $sectionId
     * @param int|null $sessionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByClassSection(int $classId, ?int $sectionId = null, ?int $sessionId = null)
    {
        $query = Student::with(['user', 'schoolClass', 'section'])
            ->where('class_id', $classId)
            ->where('is_active', true);
        
        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }
        
        return $query->orderBy('roll_number')->get();
    }

    /**
     * Search students by name or admission number.
     * 
     * @param string $search
     * @param int|null $sessionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(string $search, ?int $sessionId = null)
    {
        $query = Student::with(['user', 'schoolClass', 'section'])
            ->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->orWhere('admission_number', 'like', "%{$search}%");
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }
        
        return $query->limit(50)->get();
    }

    /**
     * Get student statistics.
     * 
     * @param int|null $sessionId
     * @return array
     */
    public function getStatistics(?int $sessionId = null): array
    {
        $query = Student::query();
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }
        
        $total = $query->count();
        $active = (clone $query)->where('is_active', true)->count();
        $inactive = (clone $query)->where('is_active', false)->count();
        $male = (clone $query)->where('gender', 'male')->count();
        $female = (clone $query)->where('gender', 'female')->count();
        $rte = (clone $query)->where('is_rte', true)->count();
        
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'male' => $male,
            'female' => $female,
            'rte' => $rte,
        ];
    }

    /**
     * Create user account for student.
     * 
     * @param array $data
     * @return User
     */
    private function createUserAccount(array $data): User
    {
        $user = User::create([
            'uuid' => Str::uuid(),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'name' => trim($data['first_name'] . ' ' . $data['last_name']),
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password'] ?? 'password123'),
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? 'India',
            'postal_code' => $data['postal_code'] ?? null,
            'is_active' => true,
        ]);
        
        $user->assignRole('student');
        
        return $user;
    }

    /**
     * Generate unique admission number.
     * 
     * @return string
     */
    private function generateAdmissionNumber(): string
    {
        $year = date('Y');
        $lastStudent = Student::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();
        
        $sequence = $lastStudent ? ((int) substr($lastStudent->admission_number, -4)) + 1 : 1;
        
        return 'ADM' . $year . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Upload student photo using FileUploadService.
     * 
     * Prompt 393: Implement Student Photo Upload
     * 
     * @param UploadedFile $file
     * @param int|null $studentId
     * @return string The stored file path
     */
    private function uploadPhoto(UploadedFile $file, ?int $studentId = null): string
    {
        $result = $this->fileUploadService->uploadStudentPhoto($file, $studentId);
        return $result['path'];
    }

    /**
     * Upload student photo and update student record.
     * 
     * Prompt 393: Implement Student Photo Upload
     * 
     * @param Student $student
     * @param UploadedFile $file
     * @return array Upload result with path and URL
     */
    public function uploadStudentPhoto(Student $student, UploadedFile $file): array
    {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'student_photo');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Delete old photo if exists
        if ($student->photo) {
            $this->fileUploadService->delete($student->photo, 'public_uploads');
        }

        // Upload new photo
        $result = $this->fileUploadService->uploadStudentPhoto($file, $student->id);

        // Update student record
        $student->update(['photo' => $result['path']]);

        return $result;
    }

    /**
     * Upload student documents using FileUploadService.
     * 
     * Prompt 394: Implement Student Document Uploads
     * 
     * @param Student $student
     * @param array $documents Array of ['file' => UploadedFile, 'type' => string, 'name' => string]
     * @return void
     */
    private function uploadDocuments(Student $student, array $documents): void
    {
        foreach ($documents as $document) {
            if (isset($document['file']) && $document['file'] instanceof UploadedFile) {
                $this->uploadStudentDocument($student, $document['file'], $document['type'] ?? 'other', $document['name'] ?? null);
            }
        }
    }

    /**
     * Upload a single student document.
     * 
     * Prompt 394: Implement Student Document Uploads
     * 
     * @param Student $student
     * @param UploadedFile $file
     * @param string $documentType
     * @param string|null $documentName
     * @return StudentDocument
     */
    public function uploadStudentDocument(
        Student $student,
        UploadedFile $file,
        string $documentType = 'other',
        ?string $documentName = null
    ): StudentDocument {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'student_document');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Upload document using FileUploadService
        $result = $this->fileUploadService->uploadStudentDocument($file, $student->id);

        // Create document record
        return StudentDocument::create([
            'student_id' => $student->id,
            'document_type' => $documentType,
            'document_name' => $documentName ?? $file->getClientOriginalName(),
            'file_path' => $result['path'],
            'original_name' => $result['original_name'],
            'mime_type' => $result['mime_type'],
            'size' => $result['size'],
            'disk' => $result['disk'],
        ]);
    }

    /**
     * Delete a student document.
     * 
     * Prompt 394: Implement Student Document Uploads
     * 
     * @param StudentDocument $document
     * @return bool
     */
    public function deleteStudentDocument(StudentDocument $document): bool
    {
        // Delete file from storage
        $this->fileUploadService->delete($document->file_path, $document->disk ?? 'private_uploads');

        // Delete record
        return $document->delete();
    }

    /**
     * Replace a student document.
     * 
     * Prompt 394: Implement Student Document Uploads
     * 
     * @param StudentDocument $document
     * @param UploadedFile $file
     * @param string|null $documentName
     * @return StudentDocument
     */
    public function replaceStudentDocument(
        StudentDocument $document,
        UploadedFile $file,
        ?string $documentName = null
    ): StudentDocument {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'student_document');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Replace file using FileUploadService
        $result = $this->fileUploadService->replace(
            $file,
            $document->file_path,
            'students/documents',
            'private_uploads'
        );

        // Update document record
        $document->update([
            'document_name' => $documentName ?? $file->getClientOriginalName(),
            'file_path' => $result['path'],
            'original_name' => $result['original_name'],
            'mime_type' => $result['mime_type'],
            'size' => $result['size'],
        ]);

        return $document->fresh();
    }

    /**
     * Get all documents for a student.
     * 
     * Prompt 394: Implement Student Document Uploads
     * 
     * @param Student $student
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStudentDocuments(Student $student)
    {
        return StudentDocument::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
