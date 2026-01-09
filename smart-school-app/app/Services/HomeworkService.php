<?php

namespace App\Services;

use App\Models\Homework;
use App\Models\HomeworkAttachment;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

/**
 * Homework Service
 * 
 * Prompt 399: Implement Homework Attachment Uploads
 * 
 * Manages homework creation, updates, and attachment uploads.
 * Uses FileUploadService for centralized file handling.
 */
class HomeworkService
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Create homework with optional attachments.
     * 
     * @param array $data
     * @param array $attachments Array of UploadedFile objects
     * @return Homework
     */
    public function create(array $data, array $attachments = []): Homework
    {
        return DB::transaction(function () use ($data, $attachments) {
            $homework = Homework::create([
                'class_id' => $data['class_id'],
                'section_id' => $data['section_id'],
                'subject_id' => $data['subject_id'],
                'academic_session_id' => $data['academic_session_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'assign_date' => $data['assign_date'] ?? now(),
                'due_date' => $data['due_date'],
                'created_by' => $data['created_by'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Upload attachments
            foreach ($attachments as $file) {
                if ($file instanceof UploadedFile) {
                    $this->uploadAttachment($homework, $file);
                }
            }

            return $homework->load('attachments');
        });
    }

    /**
     * Update homework.
     * 
     * @param Homework $homework
     * @param array $data
     * @return Homework
     */
    public function update(Homework $homework, array $data): Homework
    {
        $homework->update($data);
        return $homework->fresh();
    }

    /**
     * Upload a single attachment to homework.
     * 
     * Prompt 399: Implement Homework Attachment Uploads
     * 
     * @param Homework $homework
     * @param UploadedFile $file
     * @param string|null $description
     * @return HomeworkAttachment
     */
    public function uploadAttachment(
        Homework $homework,
        UploadedFile $file,
        ?string $description = null
    ): HomeworkAttachment {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'homework_attachment');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Upload file using FileUploadService
        $result = $this->fileUploadService->uploadHomeworkAttachment($file, $homework->id);

        // Create attachment record
        return HomeworkAttachment::create([
            'homework_id' => $homework->id,
            'file_path' => $result['path'],
            'original_name' => $result['original_name'],
            'mime_type' => $result['mime_type'],
            'size' => $result['size'],
            'disk' => $result['disk'],
            'description' => $description,
        ]);
    }

    /**
     * Upload multiple attachments to homework.
     * 
     * Prompt 399: Implement Homework Attachment Uploads
     * 
     * @param Homework $homework
     * @param array $files Array of UploadedFile objects
     * @return array Array of HomeworkAttachment objects
     */
    public function uploadAttachments(Homework $homework, array $files): array
    {
        $attachments = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $attachments[] = $this->uploadAttachment($homework, $file);
            }
        }
        return $attachments;
    }

    /**
     * Delete an attachment.
     * 
     * Prompt 399: Implement Homework Attachment Uploads
     * 
     * @param HomeworkAttachment $attachment
     * @return bool
     */
    public function deleteAttachment(HomeworkAttachment $attachment): bool
    {
        // Delete file from storage
        $this->fileUploadService->delete($attachment->file_path, $attachment->disk ?? 'private_uploads');

        // Delete record
        return $attachment->delete();
    }

    /**
     * Replace an attachment.
     * 
     * Prompt 399: Implement Homework Attachment Uploads
     * 
     * @param HomeworkAttachment $attachment
     * @param UploadedFile $file
     * @return HomeworkAttachment
     */
    public function replaceAttachment(HomeworkAttachment $attachment, UploadedFile $file): HomeworkAttachment
    {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'homework_attachment');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Replace file using FileUploadService
        $result = $this->fileUploadService->replace(
            $file,
            $attachment->file_path,
            'homework',
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
     * Get homework by class and section.
     * 
     * @param int $classId
     * @param int|null $sectionId
     * @param int|null $sessionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByClassSection(int $classId, ?int $sectionId = null, ?int $sessionId = null)
    {
        $query = Homework::with(['attachments', 'subject', 'createdBy'])
            ->where('class_id', $classId)
            ->where('is_active', true);

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }

        return $query->orderBy('due_date', 'desc')->get();
    }

    /**
     * Get homework by subject.
     * 
     * @param int $subjectId
     * @param int|null $classId
     * @param int|null $sessionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBySubject(int $subjectId, ?int $classId = null, ?int $sessionId = null)
    {
        $query = Homework::with(['attachments', 'schoolClass', 'section', 'createdBy'])
            ->where('subject_id', $subjectId)
            ->where('is_active', true);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }

        return $query->orderBy('due_date', 'desc')->get();
    }

    /**
     * Get pending homework (not yet due).
     * 
     * @param int $classId
     * @param int|null $sectionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPending(int $classId, ?int $sectionId = null)
    {
        $query = Homework::with(['attachments', 'subject'])
            ->where('class_id', $classId)
            ->where('is_active', true)
            ->where('due_date', '>=', now());

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        return $query->orderBy('due_date')->get();
    }

    /**
     * Get overdue homework.
     * 
     * @param int $classId
     * @param int|null $sectionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOverdue(int $classId, ?int $sectionId = null)
    {
        $query = Homework::with(['attachments', 'subject'])
            ->where('class_id', $classId)
            ->where('is_active', true)
            ->where('due_date', '<', now());

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        return $query->orderBy('due_date', 'desc')->get();
    }

    /**
     * Delete homework and all attachments.
     * 
     * @param Homework $homework
     * @return bool
     */
    public function delete(Homework $homework): bool
    {
        return DB::transaction(function () use ($homework) {
            // Delete all attachments
            foreach ($homework->attachments as $attachment) {
                $this->deleteAttachment($attachment);
            }

            // Delete homework
            return $homework->delete();
        });
    }
}
