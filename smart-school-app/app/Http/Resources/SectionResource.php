<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Section Resource
 * 
 * Prompt 296: Build API Resource Classes for JSON Consistency
 * 
 * Transforms Section model data into a consistent JSON format.
 */
class SectionResource extends JsonResource
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
            'display_name' => $this->display_name ?? $this->name,
            'capacity' => $this->capacity,
            'is_active' => $this->is_active,
            
            // Relationships (loaded conditionally)
            'class' => $this->whenLoaded('schoolClass', function () {
                return [
                    'id' => $this->schoolClass->id,
                    'name' => $this->schoolClass->name,
                ];
            }),
            'class_teacher' => $this->whenLoaded('classTeacher', function () {
                return [
                    'id' => $this->classTeacher->id,
                    'name' => $this->classTeacher->name,
                ];
            }),
            'students_count' => $this->whenCounted('students'),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
