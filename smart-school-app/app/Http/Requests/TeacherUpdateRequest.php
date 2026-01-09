<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * Teacher Update Request
 * 
 * Prompt 341: Create Teacher Update Form Request
 * 
 * Validates teacher update form data.
 */
class TeacherUpdateRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit-teachers');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $teacherId = $this->route('teacher');
        $teacher = is_object($teacherId) ? $teacherId : null;
        $userId = $teacher?->user_id;

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
            
            // Address Information
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'zip_code' => ['required', 'string', 'max:20'],
            
            // Employment Information
            'joining_date' => ['required', 'date'],
            'employee_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('teachers', 'employee_id')->ignore($teacher?->id),
            ],
            'designation' => ['required', 'string', 'max:100'],
            'qualification' => ['required', 'string', 'max:255'],
            'experience' => ['required', 'numeric', 'min:0'],
            'salary' => ['required', 'numeric', 'min:0'],
            
            // Photo and Documents
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
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
            'address.required' => 'The address is required.',
            'city.required' => 'The city is required.',
            'city.max' => 'The city must not exceed 100 characters.',
            'state.required' => 'The state is required.',
            'state.max' => 'The state must not exceed 100 characters.',
            'country.required' => 'The country is required.',
            'country.max' => 'The country must not exceed 100 characters.',
            'zip_code.required' => 'The zip code is required.',
            'zip_code.max' => 'The zip code must not exceed 20 characters.',
            'joining_date.required' => 'The joining date is required.',
            'joining_date.date' => 'Please enter a valid joining date.',
            'employee_id.required' => 'The employee ID is required.',
            'employee_id.max' => 'The employee ID must not exceed 50 characters.',
            'employee_id.unique' => 'This employee ID is already assigned to another teacher.',
            'designation.required' => 'The designation is required.',
            'designation.max' => 'The designation must not exceed 100 characters.',
            'qualification.required' => 'The qualification is required.',
            'qualification.max' => 'The qualification must not exceed 255 characters.',
            'experience.required' => 'The experience is required.',
            'experience.numeric' => 'The experience must be a number.',
            'experience.min' => 'The experience cannot be negative.',
            'salary.required' => 'The salary is required.',
            'salary.numeric' => 'The salary must be a number.',
            'salary.min' => 'The salary cannot be negative.',
            'photo.image' => 'The photo must be an image.',
            'photo.mimes' => 'The photo must be a file of type: jpeg, png, jpg, gif, svg.',
            'photo.max' => 'The photo size must not exceed 2MB.',
            'documents.array' => 'The documents must be an array.',
            'documents.*.file' => 'Each document must be a file.',
            'documents.*.mimes' => 'Each document must be a file of type: pdf, doc, docx.',
            'documents.*.max' => 'Each document size must not exceed 5MB.',
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
            'joining_date' => 'joining date',
            'employee_id' => 'employee ID',
        ];
    }
}
