<?php

namespace App\Events;

use App\Models\Student;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Student Updated Event
 * 
 * Prompt 455: Create Student Updated Event
 * 
 * Triggered when a student's profile is updated.
 * Logs profile changes and updates caches for data consistency.
 * 
 * Listeners:
 * - LogStudentProfileChange: Creates audit log entry for the profile change
 * - RefreshStudentCache: Invalidates and refreshes student-related caches
 */
class StudentUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The student model instance.
     */
    public Student $student;

    /**
     * The original attributes before update.
     */
    public array $originalAttributes;

    /**
     * The changed attributes.
     */
    public array $changedAttributes;

    /**
     * The user who updated the student record.
     */
    public array $updatedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(
        Student $student,
        array $originalAttributes = [],
        array $changedAttributes = [],
        array $updatedBy = []
    ) {
        $this->student = $student;
        $this->originalAttributes = $originalAttributes;
        $this->changedAttributes = $changedAttributes;
        $this->updatedBy = $updatedBy;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("student.{$this->student->id}"),
            new PrivateChannel('admin'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'student.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'student_id' => $this->student->id,
            'admission_number' => $this->student->admission_number,
            'changed_fields' => array_keys($this->changedAttributes),
            'updated_by' => $this->updatedBy,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the student instance.
     */
    public function getStudent(): Student
    {
        return $this->student;
    }

    /**
     * Get the list of changed field names.
     */
    public function getChangedFields(): array
    {
        return array_keys($this->changedAttributes);
    }

    /**
     * Check if a specific field was changed.
     */
    public function wasFieldChanged(string $field): bool
    {
        return array_key_exists($field, $this->changedAttributes);
    }

    /**
     * Get the original value of a field.
     */
    public function getOriginalValue(string $field): mixed
    {
        return $this->originalAttributes[$field] ?? null;
    }

    /**
     * Get the new value of a field.
     */
    public function getNewValue(string $field): mixed
    {
        return $this->changedAttributes[$field] ?? null;
    }
}
