<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Teacher Assigned Event
 * 
 * Prompt 456: Create Teacher Assigned Event
 * 
 * Triggered when a teacher is assigned to a class, section, or subject.
 * Notifies the teacher and updates timetable caches.
 * 
 * Listeners:
 * - SendTeacherAssignmentNotification: Sends notification to the teacher
 * - UpdateTimetableCache: Refreshes timetable cache for the affected class/section
 * - LogTeacherAssignment: Creates audit log entry for the assignment
 */
class TeacherAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The teacher user model instance.
     */
    public User $teacher;

    /**
     * The class ID the teacher is assigned to.
     */
    public ?int $classId;

    /**
     * The section ID the teacher is assigned to.
     */
    public ?int $sectionId;

    /**
     * The subject ID the teacher is assigned to.
     */
    public ?int $subjectId;

    /**
     * The assignment type (class_teacher, subject_teacher, etc.).
     */
    public string $assignmentType;

    /**
     * The academic session ID.
     */
    public ?int $academicSessionId;

    /**
     * The user who made the assignment.
     */
    public array $assignedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(
        User $teacher,
        ?int $classId = null,
        ?int $sectionId = null,
        ?int $subjectId = null,
        string $assignmentType = 'subject_teacher',
        ?int $academicSessionId = null,
        array $assignedBy = []
    ) {
        $this->teacher = $teacher;
        $this->classId = $classId;
        $this->sectionId = $sectionId;
        $this->subjectId = $subjectId;
        $this->assignmentType = $assignmentType;
        $this->academicSessionId = $academicSessionId;
        $this->assignedBy = $assignedBy;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel("teacher.{$this->teacher->id}"),
            new PrivateChannel('admin'),
        ];

        if ($this->classId && $this->sectionId) {
            $channels[] = new PrivateChannel("class.{$this->classId}.{$this->sectionId}");
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'teacher.assigned';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'teacher_id' => $this->teacher->id,
            'teacher_name' => $this->teacher->full_name,
            'class_id' => $this->classId,
            'section_id' => $this->sectionId,
            'subject_id' => $this->subjectId,
            'assignment_type' => $this->assignmentType,
            'academic_session_id' => $this->academicSessionId,
            'assigned_by' => $this->assignedBy,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the teacher instance.
     */
    public function getTeacher(): User
    {
        return $this->teacher;
    }

    /**
     * Check if this is a class teacher assignment.
     */
    public function isClassTeacherAssignment(): bool
    {
        return $this->assignmentType === 'class_teacher';
    }

    /**
     * Check if this is a subject teacher assignment.
     */
    public function isSubjectTeacherAssignment(): bool
    {
        return $this->assignmentType === 'subject_teacher';
    }
}
