<?php

namespace App\Services;

use App\Models\AssignmentSubmission;
use App\Models\Homework;
use App\Models\Student;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

/**
 * Assignment Submission Service
 * 
 * Prompt 401: Implement Assignment Submission Uploads
 * 
 * Manages student assignment submissions including file uploads,
 * grading, and feedback. Uses FileUploadService for centralized file handling.
 */
class AssignmentSubmissionService
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Submit an assignment with file upload.
     * 
     * Prompt 401: Implement Assignment Submission Uploads
     * 
     * @param Homework $homework
     * @param Student $student
     * @param UploadedFile $file
     * @param string|null $remarks
     * @return AssignmentSubmission
     */
    public function submit(
        Homework $homework,
        Student $student,
        UploadedFile $file,
        ?string $remarks = null
    ): AssignmentSubmission {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'assignment_submission');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Check if already submitted
        $existingSubmission = AssignmentSubmission::where('homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingSubmission) {
            // Update existing submission
            return $this->resubmit($existingSubmission, $file, $remarks);
        }

        // Upload file using FileUploadService
        $result = $this->fileUploadService->uploadPrivate(
            $file,
            "assignments/{$homework->id}",
            ['prefix' => "student_{$student->id}"]
        );

        // Create submission record
        return AssignmentSubmission::create([
            'homework_id' => $homework->id,
            'student_id' => $student->id,
            'file_path' => $result['path'],
            'original_name' => $result['original_name'],
            'mime_type' => $result['mime_type'],
            'size' => $result['size'],
            'disk' => $result['disk'],
            'remarks' => $remarks,
            'submitted_at' => now(),
            'is_late' => now()->isAfter($homework->due_date),
        ]);
    }

    /**
     * Resubmit an assignment (replace existing submission).
     * 
     * Prompt 401: Implement Assignment Submission Uploads
     * 
     * @param AssignmentSubmission $submission
     * @param UploadedFile $file
     * @param string|null $remarks
     * @return AssignmentSubmission
     */
    public function resubmit(
        AssignmentSubmission $submission,
        UploadedFile $file,
        ?string $remarks = null
    ): AssignmentSubmission {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'assignment_submission');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Replace file using FileUploadService
        $result = $this->fileUploadService->replace(
            $file,
            $submission->file_path,
            "assignments/{$submission->homework_id}",
            'private_uploads'
        );

        // Update submission record
        $submission->update([
            'file_path' => $result['path'],
            'original_name' => $result['original_name'],
            'mime_type' => $result['mime_type'],
            'size' => $result['size'],
            'remarks' => $remarks ?? $submission->remarks,
            'submitted_at' => now(),
            'resubmission_count' => $submission->resubmission_count + 1,
        ]);

        return $submission->fresh();
    }

    /**
     * Grade a submission.
     * 
     * @param AssignmentSubmission $submission
     * @param float $marks
     * @param int $gradedBy
     * @param string|null $feedback
     * @return AssignmentSubmission
     */
    public function grade(
        AssignmentSubmission $submission,
        float $marks,
        int $gradedBy,
        ?string $feedback = null
    ): AssignmentSubmission {
        $submission->update([
            'marks' => $marks,
            'feedback' => $feedback,
            'graded_by' => $gradedBy,
            'graded_at' => now(),
            'status' => 'graded',
        ]);

        return $submission->fresh();
    }

    /**
     * Get submissions for a homework.
     * 
     * @param Homework $homework
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByHomework(Homework $homework)
    {
        return AssignmentSubmission::with(['student', 'gradedBy'])
            ->where('homework_id', $homework->id)
            ->orderBy('submitted_at', 'desc')
            ->get();
    }

    /**
     * Get submissions by student.
     * 
     * @param Student $student
     * @param int|null $sessionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByStudent(Student $student, ?int $sessionId = null)
    {
        $query = AssignmentSubmission::with(['homework', 'homework.subject'])
            ->where('student_id', $student->id);

        if ($sessionId) {
            $query->whereHas('homework', function ($q) use ($sessionId) {
                $q->where('academic_session_id', $sessionId);
            });
        }

        return $query->orderBy('submitted_at', 'desc')->get();
    }

    /**
     * Get pending submissions (not graded).
     * 
     * @param int|null $homeworkId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPending(?int $homeworkId = null)
    {
        $query = AssignmentSubmission::with(['student', 'homework'])
            ->whereNull('graded_at');

        if ($homeworkId) {
            $query->where('homework_id', $homeworkId);
        }

        return $query->orderBy('submitted_at')->get();
    }

    /**
     * Get late submissions.
     * 
     * @param int|null $homeworkId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLateSubmissions(?int $homeworkId = null)
    {
        $query = AssignmentSubmission::with(['student', 'homework'])
            ->where('is_late', true);

        if ($homeworkId) {
            $query->where('homework_id', $homeworkId);
        }

        return $query->orderBy('submitted_at', 'desc')->get();
    }

    /**
     * Get submission statistics for a homework.
     * 
     * @param Homework $homework
     * @return array
     */
    public function getStatistics(Homework $homework): array
    {
        $submissions = AssignmentSubmission::where('homework_id', $homework->id)->get();

        return [
            'total_submissions' => $submissions->count(),
            'graded_count' => $submissions->whereNotNull('graded_at')->count(),
            'pending_count' => $submissions->whereNull('graded_at')->count(),
            'late_count' => $submissions->where('is_late', true)->count(),
            'average_marks' => $submissions->whereNotNull('marks')->avg('marks'),
            'highest_marks' => $submissions->whereNotNull('marks')->max('marks'),
            'lowest_marks' => $submissions->whereNotNull('marks')->min('marks'),
        ];
    }

    /**
     * Delete a submission.
     * 
     * @param AssignmentSubmission $submission
     * @return bool
     */
    public function delete(AssignmentSubmission $submission): bool
    {
        // Delete file from storage
        $this->fileUploadService->delete($submission->file_path, $submission->disk ?? 'private_uploads');

        // Delete record
        return $submission->delete();
    }

    /**
     * Check if student has submitted.
     * 
     * @param Homework $homework
     * @param Student $student
     * @return bool
     */
    public function hasSubmitted(Homework $homework, Student $student): bool
    {
        return AssignmentSubmission::where('homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->exists();
    }

    /**
     * Get students who haven't submitted.
     * 
     * @param Homework $homework
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMissingSubmissions(Homework $homework)
    {
        $submittedStudentIds = AssignmentSubmission::where('homework_id', $homework->id)
            ->pluck('student_id');

        return Student::where('class_id', $homework->class_id)
            ->where('section_id', $homework->section_id)
            ->where('status', 'active')
            ->whereNotIn('id', $submittedStudentIds)
            ->get();
    }
}
