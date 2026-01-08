<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Academic Session Resource
 * 
 * Prompt 296: Build API Resource Classes for JSON Consistency
 * 
 * Transforms AcademicSession model data into a consistent JSON format.
 */
class AcademicSessionResource extends JsonResource
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
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'is_current' => $this->is_current,
            'is_active' => $this->is_active,
            'description' => $this->description,
            
            // Computed properties
            'duration' => $this->start_date && $this->end_date 
                ? $this->start_date->diffInMonths($this->end_date) . ' months'
                : null,
            'status' => $this->getStatus(),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Get the session status.
     *
     * @return string
     */
    protected function getStatus(): string
    {
        if ($this->is_current) {
            return 'current';
        }
        
        if ($this->end_date && $this->end_date->isPast()) {
            return 'completed';
        }
        
        if ($this->start_date && $this->start_date->isFuture()) {
            return 'upcoming';
        }
        
        return $this->is_active ? 'active' : 'inactive';
    }
}
