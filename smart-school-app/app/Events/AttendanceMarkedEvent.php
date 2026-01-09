<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Attendance Marked Event
 * 
 * Prompt 307: Enable Real-Time Events for UI Updates
 * 
 * Broadcasts when attendance is marked for a class/section.
 * Used to update attendance dashboards and reports in real-time.
 */
class AttendanceMarkedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The class ID.
     */
    public int $classId;

    /**
     * The section ID.
     */
    public int $sectionId;

    /**
     * The attendance date.
     */
    public string $date;

    /**
     * The attendance summary.
     */
    public array $summary;

    /**
     * The user who marked the attendance.
     */
    public array $markedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $classId,
        int $sectionId,
        string $date,
        array $summary,
        array $markedBy
    ) {
        $this->classId = $classId;
        $this->sectionId = $sectionId;
        $this->date = $date;
        $this->summary = $summary;
        $this->markedBy = $markedBy;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("attendance.{$this->classId}.{$this->sectionId}"),
            new PrivateChannel('admin'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'attendance.marked';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'class_id' => $this->classId,
            'section_id' => $this->sectionId,
            'date' => $this->date,
            'summary' => $this->summary,
            'marked_by' => $this->markedBy,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
