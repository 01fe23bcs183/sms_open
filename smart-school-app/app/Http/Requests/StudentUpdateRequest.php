<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * Student Update Request
 * 
 * Prompt 339: Create Student Update Form Request
 * 
 * Validates student update form data.
 */
class StudentUpdateRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit-students');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $studentId = $this->route('student');
        $student = is_object($studentId) ? $studentId : null;
        $userId = $student?->user_id;

        return [
            // Personal Information
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['required', 'string', 'max:20'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', 'in:male,female,other'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'religion' => ['nullable', 'string', 'max:100'],
            'caste' => ['nullable', 'string', 'max:100'],
            
            // Address Information
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'zip_code' => ['required', 'string', 'max:20'],
            
            // Academic Information
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'admission_date' => ['required', 'date'],
            'roll_number' => ['required', 'string', 'max:50'],
            'admission_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'admission_number')->ignore($student?->id),
            ],
            
            // Photo
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            
            // Parent Information
            'parent_id' => ['required', 'exists:users,id'],
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
            'first_name.required' => 'The first name is required.',
            'first_name.max' => 'The first name must not exceed 255 characters.',
            'last_name.required' => 'The last name is required.',
            'last_name.max' => 'The last name must not exceed 255 characters.',
            'email.required' => 'The email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone.required' => 'The phone number is required.',
            'phone.max' => 'The phone number must not exceed 20 characters.',
            'date_of_birth.required' => 'The date of birth is required.',
            'date_of_birth.date' => 'Please enter a valid date of birth.',
            'gender.required' => 'The gender is required.',
            'gender.in' => 'Please select a valid gender (male, female, or other).',
            'blood_group.max' => 'The blood group must not exceed 10 characters.',
            'religion.max' => 'The religion must not exceed 100 characters.',
            'caste.max' => 'The caste must not exceed 100 characters.',
            'address.required' => 'The address is required.',
            'city.required' => 'The city is required.',
            'city.max' => 'The city must not exceed 100 characters.',
            'state.required' => 'The state is required.',
            'state.max' => 'The state must not exceed 100 characters.',
            'country.required' => 'The country is required.',
            'country.max' => 'The country must not exceed 100 characters.',
            'zip_code.required' => 'The zip code is required.',
            'zip_code.max' => 'The zip code must not exceed 20 characters.',
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'The selected class is invalid.',
            'section_id.required' => 'Please select a section.',
            'section_id.exists' => 'The selected section is invalid.',
            'academic_session_id.required' => 'Please select an academic session.',
            'academic_session_id.exists' => 'The selected academic session is invalid.',
            'admission_date.required' => 'The admission date is required.',
            'admission_date.date' => 'Please enter a valid admission date.',
            'roll_number.required' => 'The roll number is required.',
            'roll_number.max' => 'The roll number must not exceed 50 characters.',
            'admission_number.required' => 'The admission number is required.',
            'admission_number.max' => 'The admission number must not exceed 50 characters.',
            'admission_number.unique' => 'This admission number is already assigned to another student.',
            'photo.image' => 'The photo must be an image.',
            'photo.mimes' => 'The photo must be a file of type: jpeg, png, jpg, gif, svg.',
            'photo.max' => 'The photo size must not exceed 2MB.',
            'parent_id.required' => 'Please select a parent.',
            'parent_id.exists' => 'The selected parent is invalid.',
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
            'first_name' => 'first name',
            'last_name' => 'last name',
            'date_of_birth' => 'date of birth',
            'blood_group' => 'blood group',
            'zip_code' => 'zip code',
            'class_id' => 'class',
            'section_id' => 'section',
            'academic_session_id' => 'academic session',
            'admission_date' => 'admission date',
            'roll_number' => 'roll number',
            'admission_number' => 'admission number',
            'parent_id' => 'parent',
        ];
    }
}
