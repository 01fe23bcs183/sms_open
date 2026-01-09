<?php

namespace App\Services;

use App\Models\User;
use App\Models\ClassSubject;
use App\Models\Section;
use App\Models\ClassTimetable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

/**
 * Teacher Service
 * 
 * Prompt 324: Create Teacher Service
 * 
 * Encapsulates teacher management rules including profile management,
 * class/subject assignments, and timetable availability validation.
 */
class TeacherService
{
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
     * Upload teacher avatar.
     * 
     * @param UploadedFile $file
     * @return string
     */
    private function uploadAvatar(UploadedFile $file): string
    {
        $filename = 'teacher_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('teachers/avatars', $filename, 'public');
    }
}
