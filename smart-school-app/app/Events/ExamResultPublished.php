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
 * Exam Result Published Event
 * 
 * Prompt 459: Create Exam Result Published Event
 * 
 * Triggered when exam results are published and made available.
 * Sends notifications to students/parents and unlocks report cards.
 * 
 * Listeners:
 * - SendExamResultNotification: Sends notifications to students and parents
 * - UpdateReportCardAvailability: Updates report card availability status
 * - LogExamResultPublished: Creates audit log entry for the result publishing
 */
class ExamResultPublished implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The exam model instance.
     */
    public Exam $exam;

    /**
     * The class ID for which results are published.
     */
    public int $classId;

    /**
     * The section ID for which results are published.
     */
    public int $sectionId;

    /**
     * The result summary statistics.
     */
    public array $resultSummary;

    /**
     * The user who published the results.
     */
    public array $publishedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(
        Exam $exam,
        int $classId,
        int $sectionId,
        array $resultSummary = [],
        array $publishedBy = []
    ) {
        $this->exam = $exam;
        $this->classId = $classId;
        $this->sectionId = $sectionId;
        $this->resultSummary = $resultSummary;
        $this->publishedBy = $publishedBy;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("class.{$this->classId}.{$this->sectionId}"),
            new PrivateChannel('admin'),
            new PrivateChannel('exams'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'exam.result.published';
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
            'class_id' => $this->classId,
            'section_id' => $this->sectionId,
            'result_summary' => $this->resultSummary,
            'published_by' => $this->publishedBy,
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
     * Get the pass percentage from result summary.
     */
    public function getPassPercentage(): float
    {
        return $this->resultSummary['pass_percentage'] ?? 0.0;
    }

    /**
     * Get the total students count.
     */
    public function getTotalStudents(): int
    {
        return $this->resultSummary['total_students'] ?? 0;
    }

    /**
     * Get the passed students count.
     */
    public function getPassedStudents(): int
    {
        return $this->resultSummary['passed_students'] ?? 0;
    }

    /**
     * Get the failed students count.
     */
    public function getFailedStudents(): int
    {
        return $this->resultSummary['failed_students'] ?? 0;
    }

    /**
     * Get the class average.
     */
    public function getClassAverage(): float
    {
        return $this->resultSummary['class_average'] ?? 0.0;
    }
}
