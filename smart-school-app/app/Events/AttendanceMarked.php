<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Attendance Marked Event
 * 
 * Prompt 457: Create Attendance Marked Event
 * 
 * Triggered after attendance is marked for a class/section.
 * Sends attendance alerts to parents and updates attendance summaries.
 * 
 * Listeners:
 * - SendAttendanceNotification: Sends notifications to parents about student attendance
 * - UpdateAttendanceSummary: Updates daily/monthly attendance summary caches
 * - LogAttendanceMarked: Creates audit log entry for the attendance marking
 */
class AttendanceMarked implements ShouldBroadcast
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
     * The academic session ID.
     */
    public int $academicSessionId;

    /**
     * The attendance records.
     */
    public array $attendanceRecords;

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
        int $academicSessionId,
        array $attendanceRecords,
        array $summary,
        array $markedBy = []
    ) {
        $this->classId = $classId;
        $this->sectionId = $sectionId;
        $this->date = $date;
        $this->academicSessionId = $academicSessionId;
        $this->attendanceRecords = $attendanceRecords;
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
            'academic_session_id' => $this->academicSessionId,
            'summary' => $this->summary,
            'marked_by' => $this->markedBy,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get students who were marked absent.
     */
    public function getAbsentStudents(): array
    {
        return array_filter($this->attendanceRecords, function ($record) {
            return ($record['status'] ?? '') === 'absent';
        });
    }

    /**
     * Get students who were marked late.
     */
    public function getLateStudents(): array
    {
        return array_filter($this->attendanceRecords, function ($record) {
            return ($record['status'] ?? '') === 'late';
        });
    }

    /**
     * Get students who were marked present.
     */
    public function getPresentStudents(): array
    {
        return array_filter($this->attendanceRecords, function ($record) {
            return ($record['status'] ?? '') === 'present';
        });
    }

    /**
     * Get the attendance percentage.
     */
    public function getAttendancePercentage(): float
    {
        $total = count($this->attendanceRecords);
        if ($total === 0) {
            return 0.0;
        }

        $present = count($this->getPresentStudents());
        return round(($present / $total) * 100, 2);
    }
}
