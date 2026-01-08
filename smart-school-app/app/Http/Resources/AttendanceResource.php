<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Attendance Resource
 * 
 * Prompt 296: Build API Resource Classes for JSON Consistency
 * 
 * Transforms Attendance model data into a consistent JSON format.
 */
class AttendanceResource extends JsonResource
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
            'date' => $this->date?->format('Y-m-d'),
            'remarks' => $this->remarks,
            
            // Relationships (loaded conditionally)
            'student' => $this->whenLoaded('student', function () {
                return [
                    'id' => $this->student->id,
                    'admission_number' => $this->student->admission_number,
                    'roll_number' => $this->student->roll_number,
                    'name' => $this->student->user?->name ?? 'N/A',
                ];
            }),
            'attendance_type' => $this->whenLoaded('attendanceType', function () {
                return [
                    'id' => $this->attendanceType->id,
                    'name' => $this->attendanceType->name,
                    'code' => $this->attendanceType->code,
                    'color' => $this->attendanceType->color,
                    'is_present' => $this->attendanceType->is_present,
                ];
            }),
            'academic_session' => $this->whenLoaded('academicSession', function () {
                return [
                    'id' => $this->academicSession->id,
                    'name' => $this->academicSession->name,
                ];
            }),
            'class' => $this->whenLoaded('schoolClass', function () {
                return [
                    'id' => $this->schoolClass->id,
                    'name' => $this->schoolClass->name,
                ];
            }),
            'section' => $this->whenLoaded('section', function () {
                return [
                    'id' => $this->section->id,
                    'name' => $this->section->name,
                ];
            }),
            'marked_by' => $this->whenLoaded('markedBy', function () {
                return [
                    'id' => $this->markedBy->id,
                    'name' => $this->markedBy->name,
                ];
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
