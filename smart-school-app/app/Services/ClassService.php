<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\ClassSubject;
use App\Models\AcademicSession;
use Illuminate\Support\Facades\DB;

/**
 * Class Service
 * 
 * Prompt 325: Create Class and Section Service
 * 
 * Manages class, section, and subject relationships. Ensures consistent
 * mapping and capacity checks.
 */
class ClassService
{
    /**
     * Create a new class.
     * 
     * @param array $data
     * @return SchoolClass
     */
    public function createClass(array $data): SchoolClass
    {
        return DB::transaction(function () use ($data) {
            $maxOrder = SchoolClass::max('order_index') ?? 0;
            
            $class = SchoolClass::create([
                'academic_session_id' => $data['academic_session_id'],
                'name' => $data['name'],
                'display_name' => $data['display_name'] ?? $data['name'],
                'section_count' => $data['section_count'] ?? 0,
                'order_index' => $data['order_index'] ?? ($maxOrder + 1),
                'is_active' => $data['is_active'] ?? true,
            ]);
            
            // Create default sections if specified
            if (isset($data['sections']) && is_array($data['sections'])) {
                foreach ($data['sections'] as $sectionData) {
                    $this->createSection($class->id, $sectionData);
                }
            }
            
            return $class->load('sections');
        });
    }

    /**
     * Update a class.
     * 
     * @param SchoolClass $class
     * @param array $data
     * @return SchoolClass
     */
    public function updateClass(SchoolClass $class, array $data): SchoolClass
    {
        $class->update($data);
        return $class->fresh();
    }

    /**
     * Delete a class.
     * 
     * @param SchoolClass $class
     * @return bool
     * @throws \Exception
     */
    public function deleteClass(SchoolClass $class): bool
    {
        // Check if class has students
        if ($class->students()->count() > 0) {
            throw new \Exception('Cannot delete class with enrolled students.');
        }
        
        return DB::transaction(function () use ($class) {
            // Delete related sections
            $class->sections()->delete();
            
            // Delete class subjects
            $class->classSubjects()->delete();
            
            // Delete class
            return $class->delete();
        });
    }

    /**
     * Create a new section for a class.
     * 
     * @param int $classId
     * @param array $data
     * @return Section
     * @throws \Exception
     */
    public function createSection(int $classId, array $data): Section
    {
        return DB::transaction(function () use ($classId, $data) {
            $class = SchoolClass::findOrFail($classId);
            
            // Check for duplicate section name in same class
            $exists = Section::where('class_id', $classId)
                ->where('name', $data['name'])
                ->exists();
            
            if ($exists) {
                throw new \Exception("Section '{$data['name']}' already exists in this class.");
            }
            
            $section = Section::create([
                'class_id' => $classId,
                'name' => $data['name'],
                'display_name' => $data['display_name'] ?? $data['name'],
                'capacity' => $data['capacity'] ?? 40,
                'class_teacher_id' => $data['class_teacher_id'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
            
            // Update class section count
            $class->update([
                'section_count' => $class->sections()->count(),
            ]);
            
            return $section;
        });
    }

    /**
     * Update a section.
     * 
     * @param Section $section
     * @param array $data
     * @return Section
     * @throws \Exception
     */
    public function updateSection(Section $section, array $data): Section
    {
        // Check for duplicate section name if name is being changed
        if (isset($data['name']) && $data['name'] !== $section->name) {
            $exists = Section::where('class_id', $section->class_id)
                ->where('name', $data['name'])
                ->where('id', '!=', $section->id)
                ->exists();
            
            if ($exists) {
                throw new \Exception("Section '{$data['name']}' already exists in this class.");
            }
        }
        
        $section->update($data);
        return $section->fresh();
    }

    /**
     * Delete a section.
     * 
     * @param Section $section
     * @return bool
     * @throws \Exception
     */
    public function deleteSection(Section $section): bool
    {
        // Check if section has students
        if ($section->students()->count() > 0) {
            throw new \Exception('Cannot delete section with enrolled students.');
        }
        
        return DB::transaction(function () use ($section) {
            $classId = $section->class_id;
            
            // Delete class subjects for this section
            ClassSubject::where('section_id', $section->id)->delete();
            
            // Delete section
            $result = $section->delete();
            
            // Update class section count
            $class = SchoolClass::find($classId);
            if ($class) {
                $class->update([
                    'section_count' => $class->sections()->count(),
                ]);
            }
            
            return $result;
        });
    }

    /**
     * Create a new subject.
     * 
     * @param array $data
     * @return Subject
     */
    public function createSubject(array $data): Subject
    {
        return Subject::create([
            'name' => $data['name'],
            'code' => $data['code'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'theory',
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update a subject.
     * 
     * @param Subject $subject
     * @param array $data
     * @return Subject
     */
    public function updateSubject(Subject $subject, array $data): Subject
    {
        $subject->update($data);
        return $subject->fresh();
    }

    /**
     * Assign subjects to a class section.
     * 
     * @param int $classId
     * @param int $sectionId
     * @param array $subjectIds
     * @param int|null $teacherId
     * @return void
     */
    public function assignSubjectsToSection(int $classId, int $sectionId, array $subjectIds, ?int $teacherId = null): void
    {
        DB::transaction(function () use ($classId, $sectionId, $subjectIds, $teacherId) {
            foreach ($subjectIds as $subjectId) {
                ClassSubject::updateOrCreate(
                    [
                        'class_id' => $classId,
                        'section_id' => $sectionId,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'teacher_id' => $teacherId,
                    ]
                );
            }
        });
    }

    /**
     * Assign teacher to a class subject.
     * 
     * @param int $classSubjectId
     * @param int $teacherId
     * @return ClassSubject
     */
    public function assignTeacherToSubject(int $classSubjectId, int $teacherId): ClassSubject
    {
        $classSubject = ClassSubject::findOrFail($classSubjectId);
        $classSubject->update(['teacher_id' => $teacherId]);
        return $classSubject->fresh();
    }

    /**
     * Get all classes with sections.
     * 
     * @param int|null $sessionId
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllClasses(?int $sessionId = null, bool $activeOnly = true)
    {
        $query = SchoolClass::with(['sections', 'academicSession']);
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        return $query->orderBy('order_index')->get();
    }

    /**
     * Get sections for a class.
     * 
     * @param int $classId
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSections(int $classId, bool $activeOnly = true)
    {
        $query = Section::with('classTeacher')
            ->where('class_id', $classId);
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        return $query->orderBy('name')->get();
    }

    /**
     * Get subjects for a class section.
     * 
     * @param int $classId
     * @param int $sectionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSubjectsForSection(int $classId, int $sectionId)
    {
        return ClassSubject::with(['subject', 'teacher'])
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->get();
    }

    /**
     * Get all subjects.
     * 
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllSubjects(bool $activeOnly = true)
    {
        $query = Subject::query();
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        return $query->orderBy('name')->get();
    }

    /**
     * Check section capacity.
     * 
     * @param int $sectionId
     * @return array
     */
    public function checkSectionCapacity(int $sectionId): array
    {
        $section = Section::with('students')->findOrFail($sectionId);
        $currentCount = $section->students()->count();
        
        return [
            'section_id' => $sectionId,
            'capacity' => $section->capacity,
            'current_count' => $currentCount,
            'available' => $section->capacity - $currentCount,
            'is_full' => $currentCount >= $section->capacity,
        ];
    }

    /**
     * Get class statistics.
     * 
     * @param int|null $sessionId
     * @return array
     */
    public function getStatistics(?int $sessionId = null): array
    {
        $classQuery = SchoolClass::query();
        $sectionQuery = Section::query();
        
        if ($sessionId) {
            $classQuery->where('academic_session_id', $sessionId);
            $sectionQuery->whereHas('schoolClass', function ($q) use ($sessionId) {
                $q->where('academic_session_id', $sessionId);
            });
        }
        
        return [
            'total_classes' => $classQuery->count(),
            'active_classes' => (clone $classQuery)->where('is_active', true)->count(),
            'total_sections' => $sectionQuery->count(),
            'active_sections' => (clone $sectionQuery)->where('is_active', true)->count(),
            'total_subjects' => Subject::count(),
            'active_subjects' => Subject::where('is_active', true)->count(),
        ];
    }
}
