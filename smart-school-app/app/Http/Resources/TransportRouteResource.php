<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transport Route Resource
 * 
 * Prompt 296: Build API Resource Classes for JSON Consistency
 * 
 * Transforms TransportRoute model data into a consistent JSON format.
 */
class TransportRouteResource extends JsonResource
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
            'start_place' => $this->start_place,
            'end_place' => $this->end_place,
            'fare' => $this->fare,
            'fare_formatted' => '₹' . number_format($this->fare, 2),
            'description' => $this->description,
            'is_active' => $this->is_active,
            
            // Relationships (loaded conditionally)
            'vehicle' => $this->whenLoaded('vehicle', function () {
                return [
                    'id' => $this->vehicle->id,
                    'vehicle_number' => $this->vehicle->vehicle_number,
                    'vehicle_model' => $this->vehicle->vehicle_model,
                    'driver_name' => $this->vehicle->driver_name,
                    'driver_phone' => $this->vehicle->driver_phone,
                ];
            }),
            'stops' => $this->whenLoaded('stops', function () {
                return $this->stops->map(function ($stop) {
                    return [
                        'id' => $stop->id,
                        'name' => $stop->name,
                        'pickup_time' => $stop->pickup_time,
                        'drop_time' => $stop->drop_time,
                        'fare' => $stop->fare,
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
