<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Exam Resource
 * 
 * Prompt 296: Build API Resource Classes for JSON Consistency
 * 
 * Transforms Exam model data into a consistent JSON format.
 */
class ExamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'is_published' => $this->is_published,
            'is_active' => $this->is_active,
            
            // Computed properties
            'status' => $this->getStatus(),
            'duration' => $this->start_date && $this->end_date 
                ? $this->start_date->diffInDays($this->end_date) + 1 . ' days'
                : null,
            
            // Relationships (loaded conditionally)
            'exam_type' => $this->whenLoaded('examType', function () {
                return [
                    'id' => $this->examType->id,
                    'name' => $this->examType->name,
                    'code' => $this->examType->code,
                ];
            }),
            'academic_session' => $this->whenLoaded('academicSession', function () {
                return [
                    'id' => $this->academicSession->id,
                    'name' => $this->academicSession->name,
                ];
            }),
            'schedules' => $this->whenLoaded('schedules', function () {
                return $this->schedules->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'class_id' => $schedule->class_id,
                        'section_id' => $schedule->section_id,
                        'subject_id' => $schedule->subject_id,
                        'exam_date' => $schedule->exam_date?->format('Y-m-d'),
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'room_number' => $schedule->room_number,
                        'full_marks' => $schedule->full_marks,
                        'passing_marks' => $schedule->passing_marks,
                    ];
                });
            }),
            'schedules_count' => $this->whenCounted('schedules'),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Get the exam status.
     *
     * @return string
     */
    protected function getStatus(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }
        
        $now = now();
        
        if ($this->start_date && $this->start_date->isFuture()) {
            return 'upcoming';
        }
        
        if ($this->end_date && $this->end_date->isPast()) {
            return $this->is_published ? 'completed' : 'pending_results';
        }
        
        if ($this->start_date && $this->end_date && 
            $now->between($this->start_date, $this->end_date)) {
            return 'ongoing';
        }
        
        return 'scheduled';
    }
}
