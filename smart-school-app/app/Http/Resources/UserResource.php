<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * User Resource
 * 
 * Prompt 296: Build API Resource Classes for JSON Consistency
 * 
 * Transforms User model data into a consistent JSON format.
 */
class UserResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'is_verified' => $this->email_verified_at !== null,
            'is_active' => $this->is_active ?? true,
            
            // Profile
            'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'avatar_url' => $this->avatar 
                ? asset('storage/' . $this->avatar) 
                : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random',
            
            // Relationships (loaded conditionally)
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => ucfirst($role->name),
                    ];
                });
            }),
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->getAllPermissions()->pluck('name');
            }),
            'student' => $this->whenLoaded('student', function () {
                return [
                    'id' => $this->student->id,
                    'admission_number' => $this->student->admission_number,
                    'roll_number' => $this->student->roll_number,
                ];
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
