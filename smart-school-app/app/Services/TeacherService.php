<?php

namespace App\Services;

use App\Models\User;
use App\Models\ClassSubject;
use App\Models\Section;
use App\Models\ClassTimetable;
use App\Models\TeacherDocument;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

/**
 * Teacher Service
 * 
 * Prompt 324: Create Teacher Service
 * Prompt 395: Implement Teacher Photo Upload
 * Prompt 396: Implement Teacher Document Uploads
 * 
 * Encapsulates teacher management rules including profile management,
 * class/subject assignments, and timetable availability validation.
 * Uses FileUploadService for photo and document uploads.
 */
class TeacherService
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Create a new teacher with user account.
     * 
     * @param array $data Teacher data
     * @return User
     * @throws \Exception
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Handle avatar upload
            $avatarPath = null;
            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                $avatarPath = $this->uploadAvatar($data['avatar']);
            }
            
            // Create user account
            $user = User::create([
                'uuid' => Str::uuid(),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'name' => trim($data['first_name'] . ' ' . $data['last_name']),
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'username' => $data['username'] ?? null,
                'password' => Hash::make($data['password'] ?? 'password123'),
                'avatar' => $avatarPath,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? 'India',
                'postal_code' => $data['postal_code'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
            
            // Assign teacher role
            $user->assignRole('teacher');
            
            // Assign to classes/subjects if provided
            if (isset($data['class_subjects']) && is_array($data['class_subjects'])) {
                $this->assignClassSubjects($user, $data['class_subjects']);
            }
            
            return $user->load('roles');
        });
    }

    /**
     * Update teacher profile.
     * 
     * @param User $teacher
     * @param array $data
     * @return User
     */
    public function update(User $teacher, array $data): User
    {
        return DB::transaction(function () use ($teacher, $data) {
            // Handle avatar upload
            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                // Delete old avatar
                if ($teacher->avatar) {
                    Storage::disk('public')->delete($teacher->avatar);
                }
                $data['avatar'] = $this->uploadAvatar($data['avatar']);
            }
            
            // Update name if first/last name changed
            if (isset($data['first_name']) || isset($data['last_name'])) {
                $data['name'] = trim(
                    ($data['first_name'] ?? $teacher->first_name) . ' ' . 
                    ($data['last_name'] ?? $teacher->last_name)
                );
            }
            
            $teacher->update($data);
            
            return $teacher->fresh();
        });
    }

    /**
     * Assign teacher to class subjects.
     * 
     * @param User $teacher
     * @param array $assignments Array of ['class_id', 'section_id', 'subject_id']
     * @return void
     */
    public function assignClassSubjects(User $teacher, array $assignments): void
    {
        DB::transaction(function () use ($teacher, $assignments) {
            foreach ($assignments as $assignment) {
                ClassSubject::updateOrCreate(
                    [
                        'class_id' => $assignment['class_id'],
                        'section_id' => $assignment['section_id'],
                        'subject_id' => $assignment['subject_id'],
                    ],
                    [
                        'teacher_id' => $teacher->id,
                    ]
                );
            }
        });
    }

    /**
     * Remove teacher from class subject.
     * 
     * @param User $teacher
     * @param int $classSubjectId
     * @return bool
     */
    public function removeClassSubject(User $teacher, int $classSubjectId): bool
    {
        return ClassSubject::where('id', $classSubjectId)
            ->where('teacher_id', $teacher->id)
            ->update(['teacher_id' => null]) > 0;
    }

    /**
     * Assign teacher as class teacher for a section.
     * 
     * @param User $teacher
     * @param int $sectionId
     * @return Section
     */
    public function assignAsClassTeacher(User $teacher, int $sectionId): Section
    {
        $section = Section::findOrFail($sectionId);
        $section->update(['class_teacher_id' => $teacher->id]);
        return $section;
    }

    /**
     * Remove teacher as class teacher.
     * 
     * @param User $teacher
     * @param int $sectionId
     * @return bool
     */
    public function removeAsClassTeacher(User $teacher, int $sectionId): bool
    {
        return Section::where('id', $sectionId)
            ->where('class_teacher_id', $teacher->id)
            ->update(['class_teacher_id' => null]) > 0;
    }

    /**
     * Check if teacher is available for a timetable slot.
     * 
     * @param User $teacher
     * @param string $dayOfWeek
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeTimetableId
     * @return bool
     */
    public function isAvailableForSlot(
        User $teacher,
        string $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $excludeTimetableId = null
    ): bool {
        $query = ClassTimetable::whereHas('classSubject', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })
        ->where('day_of_week', $dayOfWeek)
        ->where(function ($q) use ($startTime, $endTime) {
            $q->whereBetween('start_time', [$startTime, $endTime])
              ->orWhereBetween('end_time', [$startTime, $endTime])
              ->orWhere(function ($q2) use ($startTime, $endTime) {
                  $q2->where('start_time', '<=', $startTime)
                     ->where('end_time', '>=', $endTime);
              });
        });
        
        if ($excludeTimetableId) {
            $query->where('id', '!=', $excludeTimetableId);
        }
        
        return $query->count() === 0;
    }

    /**
     * Get teacher's timetable.
     * 
     * @param User $teacher
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTimetable(User $teacher)
    {
        return ClassTimetable::with(['classSubject.schoolClass', 'classSubject.section', 'classSubject.subject'])
            ->whereHas('classSubject', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get teacher's assigned classes and subjects.
     * 
     * @param User $teacher
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAssignedClassSubjects(User $teacher)
    {
        return ClassSubject::with(['schoolClass', 'section', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->get();
    }

    /**
     * Get sections where teacher is class teacher.
     * 
     * @param User $teacher
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getClassTeacherSections(User $teacher)
    {
        return Section::with('schoolClass')
            ->where('class_teacher_id', $teacher->id)
            ->get();
    }

    /**
     * Get all teachers.
     * 
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(bool $activeOnly = true)
    {
        $query = User::role('teacher')->with('roles');
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        return $query->orderBy('first_name')->get();
    }

    /**
     * Search teachers by name or email.
     * 
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(string $search)
    {
        return User::role('teacher')
            ->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get();
    }

    /**
     * Deactivate a teacher.
     * 
     * @param User $teacher
     * @return User
     */
    public function deactivate(User $teacher): User
    {
        $teacher->update(['is_active' => false]);
        return $teacher;
    }

    /**
     * Reactivate a teacher.
     * 
     * @param User $teacher
     * @return User
     */
    public function reactivate(User $teacher): User
    {
        $teacher->update(['is_active' => true]);
        return $teacher;
    }

    /**
     * Get teacher statistics.
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        $total = User::role('teacher')->count();
        $active = User::role('teacher')->where('is_active', true)->count();
        $inactive = User::role('teacher')->where('is_active', false)->count();
        $classTeachers = Section::whereNotNull('class_teacher_id')->distinct('class_teacher_id')->count();
        
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'class_teachers' => $classTeachers,
        ];
    }

    /**
     * Upload teacher avatar using FileUploadService.
     * 
     * Prompt 395: Implement Teacher Photo Upload
     * 
     * @param UploadedFile $file
     * @param int|null $teacherId
     * @return string The stored file path
     */
    private function uploadAvatar(UploadedFile $file, ?int $teacherId = null): string
    {
        $result = $this->fileUploadService->uploadTeacherPhoto($file, $teacherId);
        return $result['path'];
    }

    /**
     * Upload teacher photo and update teacher record.
     * 
     * Prompt 395: Implement Teacher Photo Upload
     * 
     * @param User $teacher
     * @param UploadedFile $file
     * @return array Upload result with path and URL
     */
    public function uploadTeacherPhoto(User $teacher, UploadedFile $file): array
    {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'teacher_photo');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Delete old avatar if exists
        if ($teacher->avatar) {
            $this->fileUploadService->delete($teacher->avatar, 'public_uploads');
        }

        // Upload new photo
        $result = $this->fileUploadService->uploadTeacherPhoto($file, $teacher->id);

        // Update teacher record
        $teacher->update(['avatar' => $result['path']]);

        return $result;
    }

    /**
     * Upload a single teacher document.
     * 
     * Prompt 396: Implement Teacher Document Uploads
     * 
     * @param User $teacher
     * @param UploadedFile $file
     * @param string $documentType
     * @param string|null $documentName
     * @param string|null $expiryDate
     * @return TeacherDocument
     */
    public function uploadTeacherDocument(
        User $teacher,
        UploadedFile $file,
        string $documentType = 'other',
        ?string $documentName = null,
        ?string $expiryDate = null
    ): TeacherDocument {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'teacher_document');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Upload document using FileUploadService
        $result = $this->fileUploadService->uploadTeacherDocument($file, $teacher->id);

        // Create document record
        return TeacherDocument::create([
            'user_id' => $teacher->id,
            'document_type' => $documentType,
            'document_name' => $documentName ?? $file->getClientOriginalName(),
            'file_path' => $result['path'],
            'original_name' => $result['original_name'],
            'mime_type' => $result['mime_type'],
            'size' => $result['size'],
            'disk' => $result['disk'],
            'expiry_date' => $expiryDate,
        ]);
    }

    /**
     * Delete a teacher document.
     * 
     * Prompt 396: Implement Teacher Document Uploads
     * 
     * @param TeacherDocument $document
     * @return bool
     */
    public function deleteTeacherDocument(TeacherDocument $document): bool
    {
        // Delete file from storage
        $this->fileUploadService->delete($document->file_path, $document->disk ?? 'private_uploads');

        // Delete record
        return $document->delete();
    }

    /**
     * Replace a teacher document.
     * 
     * Prompt 396: Implement Teacher Document Uploads
     * 
     * @param TeacherDocument $document
     * @param UploadedFile $file
     * @param string|null $documentName
     * @return TeacherDocument
     */
    public function replaceTeacherDocument(
        TeacherDocument $document,
        UploadedFile $file,
        ?string $documentName = null
    ): TeacherDocument {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'teacher_document');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Replace file using FileUploadService
        $result = $this->fileUploadService->replace(
            $file,
            $document->file_path,
            'teachers/documents',
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
     * Get all documents for a teacher.
     * 
     * Prompt 396: Implement Teacher Document Uploads
     * 
     * @param User $teacher
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTeacherDocuments(User $teacher)
    {
        return TeacherDocument::where('user_id', $teacher->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get expiring documents for a teacher.
     * 
     * Prompt 396: Implement Teacher Document Uploads
     * 
     * @param User $teacher
     * @param int $daysAhead
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getExpiringDocuments(User $teacher, int $daysAhead = 30)
    {
        return TeacherDocument::where('user_id', $teacher->id)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($daysAhead))
            ->where('expiry_date', '>=', now())
            ->orderBy('expiry_date')
            ->get();
    }
}
