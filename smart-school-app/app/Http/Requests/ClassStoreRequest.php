<?php

namespace App\Http\Requests;

/**
 * Class Store Request
 * 
 * Prompt 342: Create Class Store Form Request
 * 
 * Validates class creation form data.
 */
class ClassStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-classes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Class Information
            'name' => ['required', 'string', 'max:100', 'unique:classes,name'],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            
            // Related Entities
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['nullable', 'exists:sections,id'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['nullable', 'exists:subjects,id'],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => ['nullable', 'exists:users,id'],
            
            // Additional Information
            'capacity' => ['nullable', 'numeric', 'min:1'],
            'room_number' => ['nullable', 'string', 'max:50'],
            'floor' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
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
            'name.required' => 'The class name is required.',
            'name.max' => 'The class name must not exceed 100 characters.',
            'name.unique' => 'This class name already exists.',
            'academic_session_id.required' => 'Please select an academic session.',
            'academic_session_id.exists' => 'The selected academic session is invalid.',
            'section_ids.array' => 'The sections must be an array.',
            'section_ids.*.exists' => 'One or more selected sections are invalid.',
            'subject_ids.array' => 'The subjects must be an array.',
            'subject_ids.*.exists' => 'One or more selected subjects are invalid.',
            'teacher_ids.array' => 'The teachers must be an array.',
            'teacher_ids.*.exists' => 'One or more selected teachers are invalid.',
            'capacity.numeric' => 'The capacity must be a number.',
            'capacity.min' => 'The capacity must be at least 1.',
            'room_number.max' => 'The room number must not exceed 50 characters.',
            'floor.max' => 'The floor must not exceed 50 characters.',
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
            'academic_session_id' => 'academic session',
            'section_ids' => 'sections',
            'subject_ids' => 'subjects',
            'teacher_ids' => 'teachers',
            'room_number' => 'room number',
        ];
    }
}
