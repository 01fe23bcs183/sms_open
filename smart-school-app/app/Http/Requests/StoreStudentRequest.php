<?php

namespace App\Http\Requests;

/**
 * Store Student Request
 * 
 * Prompt 297: Standardize Validation Errors for Web and JSON
 * 
 * Validates student creation requests.
 */
class StoreStudentRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // User Information
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            
            // Student Information
            'admission_number' => ['required', 'string', 'max:50', 'unique:students,admission_number'],
            'roll_number' => ['nullable', 'string', 'max:50'],
            'date_of_admission' => ['required', 'date'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female,other'],
            'blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'religion' => ['nullable', 'string', 'max:100'],
            'caste' => ['nullable', 'string', 'max:100'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'mother_tongue' => ['nullable', 'string', 'max:100'],
            
            // Academic Information
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'category_id' => ['nullable', 'exists:student_categories,id'],
            
            // Father Information
            'father_name' => ['nullable', 'string', 'max:255'],
            'father_phone' => ['nullable', 'string', 'max:20'],
            'father_occupation' => ['nullable', 'string', 'max:255'],
            'father_email' => ['nullable', 'email', 'max:255'],
            'father_qualification' => ['nullable', 'string', 'max:255'],
            'father_annual_income' => ['nullable', 'numeric', 'min:0'],
            
            // Mother Information
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_phone' => ['nullable', 'string', 'max:20'],
            'mother_occupation' => ['nullable', 'string', 'max:255'],
            'mother_email' => ['nullable', 'email', 'max:255'],
            'mother_qualification' => ['nullable', 'string', 'max:255'],
            'mother_annual_income' => ['nullable', 'numeric', 'min:0'],
            
            // Guardian Information
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'guardian_relation' => ['nullable', 'string', 'max:100'],
            'guardian_occupation' => ['nullable', 'string', 'max:255'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'guardian_address' => ['nullable', 'string', 'max:500'],
            
            // Current Address
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            
            // Permanent Address
            'permanent_address' => ['nullable', 'string', 'max:500'],
            'permanent_city' => ['nullable', 'string', 'max:100'],
            'permanent_state' => ['nullable', 'string', 'max:100'],
            'permanent_country' => ['nullable', 'string', 'max:100'],
            'permanent_postal_code' => ['nullable', 'string', 'max:20'],
            
            // Previous School Information
            'previous_school_name' => ['nullable', 'string', 'max:255'],
            'previous_school_address' => ['nullable', 'string', 'max:500'],
            'previous_class' => ['nullable', 'string', 'max:100'],
            'transfer_certificate_number' => ['nullable', 'string', 'max:100'],
            'transfer_certificate_date' => ['nullable', 'date'],
            
            // Additional Information
            'is_rte' => ['nullable', 'boolean'],
            'admission_type' => ['nullable', 'in:new,transfer,readmission'],
            
            // Emergency Contact
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:100'],
            
            // Medical Information
            'medical_notes' => ['nullable', 'string', 'max:1000'],
            'allergies' => ['nullable', 'string', 'max:500'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'identification_marks' => ['nullable', 'string', 'max:500'],
            
            // Bank Information
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_ifsc_code' => ['nullable', 'string', 'max:20'],
            
            // Documents
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'aadhar_number' => ['nullable', 'string', 'max:20'],
            
            // Status
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    protected function customMessages(): array
    {
        return [
            'admission_number.unique' => 'This admission number is already assigned to another student.',
            'email.unique' => 'This email address is already registered.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'photo.max' => 'Photo size must not exceed 2MB.',
        ];
    }

    /**
     * Get custom attribute names.
     *
     * @return array
     */
    protected function customAttributes(): array
    {
        return [
            'is_rte' => 'RTE status',
            'aadhar_number' => 'Aadhar number',
            'bank_ifsc_code' => 'bank IFSC code',
        ];
    }
}
