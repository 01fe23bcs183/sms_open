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
 * Student Created Event
 * 
 * Prompt 454: Create Student Created Event
 * 
 * Triggered after a new student is admitted to the school.
 * Sends welcome notification and logs audit entry for the admission.
 * 
 * Listeners:
 * - SendStudentWelcomeNotification: Sends welcome email/SMS to student and parents
 * - LogStudentAdmission: Creates audit log entry for the admission
 */
class StudentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The student model instance.
     */
    public Student $student;

    /**
     * The user who created the student record.
     */
    public array $createdBy;

    /**
     * Additional metadata for the event.
     */
    public array $metadata;

    /**
     * Create a new event instance.
     */
    public function __construct(Student $student, array $createdBy = [], array $metadata = [])
    {
        $this->student = $student;
        $this->createdBy = $createdBy;
        $this->metadata = $metadata;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin'),
            new PrivateChannel('students'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'student.created';
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
            'student_name' => $this->student->full_name,
            'class_id' => $this->student->class_id,
            'section_id' => $this->student->section_id,
            'created_by' => $this->createdBy,
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
     * Get the student's parent/guardian contact information.
     */
    public function getParentContacts(): array
    {
        return [
            'father' => [
                'name' => $this->student->father_name,
                'phone' => $this->student->father_phone,
                'email' => $this->student->father_email,
            ],
            'mother' => [
                'name' => $this->student->mother_name,
                'phone' => $this->student->mother_phone,
                'email' => $this->student->mother_email,
            ],
            'guardian' => [
                'name' => $this->student->guardian_name,
                'phone' => $this->student->guardian_phone,
                'email' => $this->student->guardian_email,
            ],
        ];
    }
}
