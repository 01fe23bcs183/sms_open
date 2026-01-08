<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Notification Resource
 * 
 * Prompt 296: Build API Resource Classes for JSON Consistency
 * 
 * Transforms Notification model data into a consistent JSON format.
 */
class NotificationResource extends JsonResource
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
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'data' => $this->data,
            'read_at' => $this->read_at?->toISOString(),
            'is_read' => $this->read_at !== null,
            
            // Computed properties
            'title' => $this->data['title'] ?? 'Notification',
            'message' => $this->data['message'] ?? '',
            'action_url' => $this->data['action_url'] ?? null,
            'icon' => $this->data['icon'] ?? 'bell',
            
            // Time ago
            'time_ago' => $this->created_at?->diffForHumans(),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Get the notification type label.
     *
     * @return string
     */
    protected function getTypeLabel(): string
    {
        $type = class_basename($this->type);
        return str_replace('Notification', '', $type);
    }
}
