<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Subject Resource
 * 
 * Prompt 296: Build API Resource Classes for JSON Consistency
 * 
 * Transforms Subject model data into a consistent JSON format.
 */
class SubjectResource extends JsonResource
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
            'code' => $this->code,
            'description' => $this->description,
            'type' => $this->type,
            'is_active' => $this->is_active,
            
            // Relationships (loaded conditionally)
            'subject_group' => $this->whenLoaded('subjectGroup', function () {
                return [
                    'id' => $this->subjectGroup->id,
                    'name' => $this->subjectGroup->name,
                ];
            }),
            'teachers' => $this->whenLoaded('teachers', function () {
                return $this->teachers->map(function ($teacher) {
                    return [
                        'id' => $teacher->id,
                        'name' => $teacher->name,
                    ];
                });
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
