<?php

namespace App\Events;

use App\Models\Exam;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Exam Scheduled Event
 * 
 * Prompt 458: Create Exam Scheduled Event
 * 
 * Triggered when a new exam schedule is published.
 * Sends alerts to students and teachers, and updates calendar feeds.
 * 
 * Listeners:
 * - SendExamScheduleNotification: Sends notifications to students and teachers
 * - LogExamScheduled: Creates audit log entry for the exam scheduling
 */
class ExamScheduled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The exam model instance.
     */
    public Exam $exam;

    /**
     * The exam schedules data.
     */
    public array $schedules;

    /**
     * The class IDs affected by this exam.
     */
    public array $classIds;

    /**
     * The section IDs affected by this exam.
     */
    public array $sectionIds;

    /**
     * The user who scheduled the exam.
     */
    public array $scheduledBy;

    /**
     * Create a new event instance.
     */
    public function __construct(
        Exam $exam,
        array $schedules = [],
        array $classIds = [],
        array $sectionIds = [],
        array $scheduledBy = []
    ) {
        $this->exam = $exam;
        $this->schedules = $schedules;
        $this->classIds = $classIds;
        $this->sectionIds = $sectionIds;
        $this->scheduledBy = $scheduledBy;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin'),
            new PrivateChannel('exams'),
        ];

        foreach ($this->classIds as $classId) {
            $channels[] = new PrivateChannel("class.{$classId}");
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'exam.scheduled';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'exam_id' => $this->exam->id,
            'exam_name' => $this->exam->name,
            'exam_type' => $this->exam->examType->name ?? null,
            'start_date' => $this->exam->start_date,
            'end_date' => $this->exam->end_date,
            'class_ids' => $this->classIds,
            'section_ids' => $this->sectionIds,
            'schedule_count' => count($this->schedules),
            'scheduled_by' => $this->scheduledBy,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the exam instance.
     */
    public function getExam(): Exam
    {
        return $this->exam;
    }

    /**
     * Get the exam schedules.
     */
    public function getSchedules(): array
    {
        return $this->schedules;
    }

    /**
     * Get the exam date range as a formatted string.
     */
    public function getDateRange(): string
    {
        $startDate = $this->exam->start_date?->format('d M Y') ?? 'TBD';
        $endDate = $this->exam->end_date?->format('d M Y') ?? 'TBD';
        return "{$startDate} - {$endDate}";
    }
}
