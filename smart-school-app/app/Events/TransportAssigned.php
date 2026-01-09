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
 * Transport Assigned Event
 * 
 * Prompt 464: Create Transport Assigned Event
 * 
 * Triggered when a student is assigned to a transport route.
 * Updates vehicle occupancy and sends assignment notification.
 * 
 * Listeners:
 * - SendTransportAssignmentNotification: Sends notification to student/parents
 * - UpdateVehicleOccupancy: Updates vehicle occupancy counts
 * - LogTransportAssignment: Creates audit log entry for the assignment
 */
class TransportAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The student model instance.
     */
    public Student $student;

    /**
     * The route ID.
     */
    public int $routeId;

    /**
     * The vehicle ID.
     */
    public ?int $vehicleId;

    /**
     * The stop ID.
     */
    public ?int $stopId;

    /**
     * The transport assignment details.
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
        int $routeId,
        ?int $vehicleId = null,
        ?int $stopId = null,
        array $assignmentDetails = [],
        array $assignedBy = []
    ) {
        $this->student = $student;
        $this->routeId = $routeId;
        $this->vehicleId = $vehicleId;
        $this->stopId = $stopId;
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
            new PrivateChannel("transport.route.{$this->routeId}"),
            new PrivateChannel('transport'),
            new PrivateChannel('admin'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'transport.assigned';
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
            'route_id' => $this->routeId,
            'vehicle_id' => $this->vehicleId,
            'stop_id' => $this->stopId,
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
     * Get the route name.
     */
    public function getRouteName(): ?string
    {
        return $this->assignmentDetails['route_name'] ?? null;
    }

    /**
     * Get the vehicle number.
     */
    public function getVehicleNumber(): ?string
    {
        return $this->assignmentDetails['vehicle_number'] ?? null;
    }

    /**
     * Get the stop name.
     */
    public function getStopName(): ?string
    {
        return $this->assignmentDetails['stop_name'] ?? null;
    }

    /**
     * Get the pickup time.
     */
    public function getPickupTime(): ?string
    {
        return $this->assignmentDetails['pickup_time'] ?? null;
    }

    /**
     * Get the transport fee.
     */
    public function getTransportFee(): float
    {
        return $this->assignmentDetails['transport_fee'] ?? 0.0;
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
