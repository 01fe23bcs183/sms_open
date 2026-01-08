<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class Resource
 * 
 * Prompt 296: Build API Resource Classes for JSON Consistency
 * 
 * Transforms SchoolClass model data into a consistent JSON format.
 */
class ClassResource extends JsonResource
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
            'numeric_name' => $this->numeric_name,
            'description' => $this->description,
            'order' => $this->order,
            'is_active' => $this->is_active,
            
            // Relationships (loaded conditionally)
            'sections' => $this->whenLoaded('sections', function () {
                return SectionResource::collection($this->sections);
            }),
            'subjects' => $this->whenLoaded('subjects', function () {
                return $this->subjects->map(function ($subject) {
                    return [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'code' => $subject->code,
                    ];
                });
            }),
            'students_count' => $this->whenCounted('students'),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
