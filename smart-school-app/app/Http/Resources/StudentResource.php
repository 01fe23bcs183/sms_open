<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Student Resource
 * 
 * Prompt 296: Build API Resource Classes for JSON Consistency
 * 
 * Transforms Student model data into a consistent JSON format.
 */
class StudentResource extends JsonResource
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
            'admission_number' => $this->admission_number,
            'roll_number' => $this->roll_number,
            'date_of_admission' => $this->date_of_admission?->format('Y-m-d'),
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'age' => $this->date_of_birth?->age,
            'gender' => $this->gender,
            'blood_group' => $this->blood_group,
            'religion' => $this->religion,
            'caste' => $this->caste,
            'nationality' => $this->nationality,
            'mother_tongue' => $this->mother_tongue,
            
            // Guardian Information
            'father' => [
                'name' => $this->father_name,
                'phone' => $this->father_phone,
                'occupation' => $this->father_occupation,
                'email' => $this->father_email,
                'qualification' => $this->father_qualification,
                'annual_income' => $this->father_annual_income,
            ],
            'mother' => [
                'name' => $this->mother_name,
                'phone' => $this->mother_phone,
                'occupation' => $this->mother_occupation,
                'email' => $this->mother_email,
                'qualification' => $this->mother_qualification,
                'annual_income' => $this->mother_annual_income,
            ],
            'guardian' => [
                'name' => $this->guardian_name,
                'phone' => $this->guardian_phone,
                'relation' => $this->guardian_relation,
                'occupation' => $this->guardian_occupation,
                'email' => $this->guardian_email,
                'address' => $this->guardian_address,
            ],
            
            // Address Information
            'current_address' => [
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'postal_code' => $this->postal_code,
            ],
            'permanent_address' => [
                'address' => $this->permanent_address,
                'city' => $this->permanent_city,
                'state' => $this->permanent_state,
                'country' => $this->permanent_country,
                'postal_code' => $this->permanent_postal_code,
            ],
            
            // Previous School Information
            'previous_school' => [
                'name' => $this->previous_school_name,
                'address' => $this->previous_school_address,
                'class' => $this->previous_class,
                'transfer_certificate_number' => $this->transfer_certificate_number,
                'transfer_certificate_date' => $this->transfer_certificate_date?->format('Y-m-d'),
            ],
            
            // Additional Information
            'is_rte' => $this->is_rte,
            'admission_type' => $this->admission_type,
            'emergency_contact' => [
                'name' => $this->emergency_contact_name,
                'phone' => $this->emergency_contact_phone,
                'relation' => $this->emergency_contact_relation,
            ],
            
            // Medical Information
            'medical' => [
                'notes' => $this->medical_notes,
                'allergies' => $this->allergies,
                'height' => $this->height,
                'weight' => $this->weight,
                'identification_marks' => $this->identification_marks,
            ],
            
            // Bank Information
            'bank' => [
                'name' => $this->bank_name,
                'account_number' => $this->bank_account_number,
                'ifsc_code' => $this->bank_ifsc_code,
            ],
            
            // Documents
            'photo' => $this->photo ? asset('storage/' . $this->photo) : null,
            'birth_certificate' => $this->birth_certificate,
            'aadhar_number' => $this->aadhar_number,
            
            // Status
            'is_active' => $this->is_active,
            
            // Relationships (loaded conditionally)
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'academic_session' => $this->whenLoaded('academicSession', function () {
                return new AcademicSessionResource($this->academicSession);
            }),
            'class' => $this->whenLoaded('schoolClass', function () {
                return new ClassResource($this->schoolClass);
            }),
            'section' => $this->whenLoaded('section', function () {
                return new SectionResource($this->section);
            }),
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'siblings' => $this->whenLoaded('siblings', function () {
                return $this->siblings->map(function ($sibling) {
                    return [
                        'id' => $sibling->id,
                        'sibling_id' => $sibling->sibling_id,
                        'relation' => $sibling->relation,
                    ];
                });
            }),
            'documents' => $this->whenLoaded('documents', function () {
                return $this->documents->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'file_path' => $doc->file_path,
                    ];
                });
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
