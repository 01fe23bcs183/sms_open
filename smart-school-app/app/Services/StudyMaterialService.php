<?php

namespace App\Services;

use App\Models\StudyMaterial;
use App\Models\StudyMaterialAttachment;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

/**
 * Study Material Service
 * 
 * Prompt 400: Implement Study Material Uploads
 * 
 * Manages study material creation, updates, and file uploads.
 * Uses FileUploadService for centralized file handling.
 */
class StudyMaterialService
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Create study material with optional attachments.
     * 
     * @param array $data
     * @param array $attachments Array of UploadedFile objects
     * @return StudyMaterial
     */
    public function create(array $data, array $attachments = []): StudyMaterial
    {
        return DB::transaction(function () use ($data, $attachments) {
            $material = StudyMaterial::create([
                'class_id' => $data['class_id'],
                'section_id' => $data['section_id'] ?? null,
                'subject_id' => $data['subject_id'],
                'academic_session_id' => $data['academic_session_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'material_type' => $data['material_type'] ?? 'document',
                'created_by' => $data['created_by'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'published_at' => $data['published_at'] ?? now(),
            ]);

            // Upload attachments
            foreach ($attachments as $file) {
                if ($file instanceof UploadedFile) {
                    $this->uploadAttachment($material, $file);
                }
            }

            return $material->load('attachments');
        });
    }

    /**
     * Update study material.
     * 
     * @param StudyMaterial $material
     * @param array $data
     * @return StudyMaterial
     */
    public function update(StudyMaterial $material, array $data): StudyMaterial
    {
        $material->update($data);
        return $material->fresh();
    }

    /**
     * Upload a single attachment to study material.
     * 
     * Prompt 400: Implement Study Material Uploads
     * 
     * @param StudyMaterial $material
     * @param UploadedFile $file
     * @param string|null $description
     * @return StudyMaterialAttachment
     */
    public function uploadAttachment(
        StudyMaterial $material,
        UploadedFile $file,
        ?string $description = null
    ): StudyMaterialAttachment {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'study_material');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Upload file using FileUploadService
        $result = $this->fileUploadService->uploadStudyMaterial($file, $material->id);

        // Create attachment record
        return StudyMaterialAttachment::create([
            'study_material_id' => $material->id,
            'file_path' => $result['path'],
            'original_name' => $result['original_name'],
            'mime_type' => $result['mime_type'],
            'size' => $result['size'],
            'disk' => $result['disk'],
            'description' => $description,
        ]);
    }

    /**
     * Upload multiple attachments to study material.
     * 
     * Prompt 400: Implement Study Material Uploads
     * 
     * @param StudyMaterial $material
     * @param array $files Array of UploadedFile objects
     * @return array Array of StudyMaterialAttachment objects
     */
    public function uploadAttachments(StudyMaterial $material, array $files): array
    {
        $attachments = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $attachments[] = $this->uploadAttachment($material, $file);
            }
        }
        return $attachments;
    }

    /**
     * Delete an attachment.
     * 
     * Prompt 400: Implement Study Material Uploads
     * 
     * @param StudyMaterialAttachment $attachment
     * @return bool
     */
    public function deleteAttachment(StudyMaterialAttachment $attachment): bool
    {
        // Delete file from storage
        $this->fileUploadService->delete($attachment->file_path, $attachment->disk ?? 'private_uploads');

        // Delete record
        return $attachment->delete();
    }

    /**
     * Replace an attachment.
     * 
     * Prompt 400: Implement Study Material Uploads
     * 
     * @param StudyMaterialAttachment $attachment
     * @param UploadedFile $file
     * @return StudyMaterialAttachment
     */
    public function replaceAttachment(StudyMaterialAttachment $attachment, UploadedFile $file): StudyMaterialAttachment
    {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'study_material');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Replace file using FileUploadService
        $result = $this->fileUploadService->replace(
            $file,
            $attachment->file_path,
            'study-materials',
            'private_uploads'
        );

        // Update attachment record
        $attachment->update([
            'file_path' => $result['path'],
            'original_name' => $result['original_name'],
            'mime_type' => $result['mime_type'],
            'size' => $result['size'],
        ]);

        return $attachment->fresh();
    }

    /**
     * Get study materials by class and section.
     * 
     * @param int $classId
     * @param int|null $sectionId
     * @param int|null $sessionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByClassSection(int $classId, ?int $sectionId = null, ?int $sessionId = null)
    {
        $query = StudyMaterial::with(['attachments', 'subject', 'createdBy'])
            ->where('class_id', $classId)
            ->where('is_active', true);

        if ($sectionId) {
            $query->where(function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId)
                    ->orWhereNull('section_id');
            });
        }

        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }

        return $query->orderBy('published_at', 'desc')->get();
    }

    /**
     * Get study materials by subject.
     * 
     * @param int $subjectId
     * @param int|null $classId
     * @param int|null $sessionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBySubject(int $subjectId, ?int $classId = null, ?int $sessionId = null)
    {
        $query = StudyMaterial::with(['attachments', 'schoolClass', 'section', 'createdBy'])
            ->where('subject_id', $subjectId)
            ->where('is_active', true);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }

        return $query->orderBy('published_at', 'desc')->get();
    }

    /**
     * Get study materials by type.
     * 
     * @param string $type
     * @param int|null $classId
     * @param int|null $subjectId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByType(string $type, ?int $classId = null, ?int $subjectId = null)
    {
        $query = StudyMaterial::with(['attachments', 'subject', 'schoolClass'])
            ->where('material_type', $type)
            ->where('is_active', true);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        return $query->orderBy('published_at', 'desc')->get();
    }

    /**
     * Get recent study materials.
     * 
     * @param int $limit
     * @param int|null $classId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecent(int $limit = 10, ?int $classId = null)
    {
        $query = StudyMaterial::with(['attachments', 'subject', 'schoolClass'])
            ->where('is_active', true)
            ->where('published_at', '<=', now());

        if ($classId) {
            $query->where('class_id', $classId);
        }

        return $query->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Delete study material and all attachments.
     * 
     * @param StudyMaterial $material
     * @return bool
     */
    public function delete(StudyMaterial $material): bool
    {
        return DB::transaction(function () use ($material) {
            // Delete all attachments
            foreach ($material->attachments as $attachment) {
                $this->deleteAttachment($attachment);
            }

            // Delete material
            return $material->delete();
        });
    }

    /**
     * Publish a study material.
     * 
     * @param StudyMaterial $material
     * @return StudyMaterial
     */
    public function publish(StudyMaterial $material): StudyMaterial
    {
        $material->update([
            'is_active' => true,
            'published_at' => now(),
        ]);
        return $material->fresh();
    }

    /**
     * Unpublish a study material.
     * 
     * @param StudyMaterial $material
     * @return StudyMaterial
     */
    public function unpublish(StudyMaterial $material): StudyMaterial
    {
        $material->update(['is_active' => false]);
        return $material->fresh();
    }
}
