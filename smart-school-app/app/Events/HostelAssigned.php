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
 * Hostel Assigned Event
 * 
 * Prompt 465: Create Hostel Assigned Event
 * 
 * Triggered when a student is assigned to a hostel room.
 * Updates room occupancy and sends allocation notification.
 * 
 * Listeners:
 * - SendHostelAssignmentNotification: Sends notification to student/parents
 * - UpdateRoomOccupancy: Updates room occupancy status
 * - LogHostelAssignment: Creates audit log entry for the assignment
 */
class HostelAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The student model instance.
     */
    public Student $student;

    /**
     * The hostel ID.
     */
    public int $hostelId;

    /**
     * The room ID.
     */
    public int $roomId;

    /**
     * The hostel assignment details.
     */
    public array $assignmentDetails;

    /**
     * The user who made the assignment.
     */
    public array $assignedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(
        Student $student,
        int $hostelId,
        int $roomId,
        array $assignmentDetails = [],
        array $assignedBy = []
    ) {
        $this->student = $student;
        $this->hostelId = $hostelId;
        $this->roomId = $roomId;
        $this->assignmentDetails = $assignmentDetails;
        $this->assignedBy = $assignedBy;
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
            new PrivateChannel("hostel.{$this->hostelId}"),
            new PrivateChannel('hostel'),
            new PrivateChannel('admin'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'hostel.assigned';
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
            'student_name' => $this->student->full_name,
            'admission_number' => $this->student->admission_number,
            'hostel_id' => $this->hostelId,
            'room_id' => $this->roomId,
            'assignment_details' => $this->assignmentDetails,
            'assigned_by' => $this->assignedBy,
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
     * Get the hostel name.
     */
    public function getHostelName(): ?string
    {
        return $this->assignmentDetails['hostel_name'] ?? null;
    }

    /**
     * Get the room number.
     */
    public function getRoomNumber(): ?string
    {
        return $this->assignmentDetails['room_number'] ?? null;
    }

    /**
     * Get the room type.
     */
    public function getRoomType(): ?string
    {
        return $this->assignmentDetails['room_type'] ?? null;
    }

    /**
     * Get the bed number.
     */
    public function getBedNumber(): ?string
    {
        return $this->assignmentDetails['bed_number'] ?? null;
    }

    /**
     * Get the hostel fee.
     */
    public function getHostelFee(): float
    {
        return $this->assignmentDetails['hostel_fee'] ?? 0.0;
    }

    /**
     * Get the check-in date.
     */
    public function getCheckInDate(): ?string
    {
        return $this->assignmentDetails['check_in_date'] ?? null;
    }

    /**
     * Get parent contact information for notifications.
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
