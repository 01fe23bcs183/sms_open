<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Teacher Resource
 * 
 * Prompt 296: Build API Resource Classes for JSON Consistency
 * 
 * Transforms Teacher/User model data into a consistent JSON format.
 */
class TeacherResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'designation' => $this->designation,
            'department' => $this->department,
            'qualification' => $this->qualification,
            'experience' => $this->experience,
            'specialization' => $this->specialization,
            
            // Personal Information
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'blood_group' => $this->blood_group,
            'marital_status' => $this->marital_status,
            'nationality' => $this->nationality,
            
            // Address
            'address' => [
                'street' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'postal_code' => $this->postal_code,
            ],
            
            // Employment Details
            'date_of_joining' => $this->date_of_joining?->format('Y-m-d'),
            'employment_type' => $this->employment_type,
            'salary' => $this->salary,
            
            // Bank Details
            'bank' => [
                'name' => $this->bank_name,
                'account_number' => $this->bank_account_number,
                'ifsc_code' => $this->bank_ifsc_code,
            ],
            
            // Documents
            'photo' => $this->photo ? asset('storage/' . $this->photo) : null,
            'aadhar_number' => $this->aadhar_number,
            'pan_number' => $this->pan_number,
            
            // Status
            'is_active' => $this->is_active ?? true,
            
            // Relationships (loaded conditionally)
            'subjects' => $this->whenLoaded('subjects', function () {
                return $this->subjects->map(function ($subject) {
                    return [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'code' => $subject->code,
                    ];
                });
            }),
            'classes' => $this->whenLoaded('classSubjects', function () {
                return $this->classSubjects->map(function ($cs) {
                    return [
                        'class_id' => $cs->class_id,
                        'section_id' => $cs->section_id,
                        'subject_id' => $cs->subject_id,
                    ];
                });
            }),
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
