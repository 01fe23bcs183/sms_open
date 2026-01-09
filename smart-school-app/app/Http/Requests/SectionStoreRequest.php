<?php

namespace App\Http\Requests;

/**
 * Section Store Request
 * 
 * Prompt 359: Create Section Store Form Request
 * 
 * Validates section creation data.
 */
class SectionStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-sections');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Section Information
            'class_id' => ['required', 'exists:classes,id'],
            'name' => ['required', 'string', 'max:100'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            
            // Class Teacher
            'class_teacher_id' => ['nullable', 'exists:users,id'],
            
            // Status
            'status' => ['required', 'in:active,inactive'],
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
            'class_id.required' => 'The class is required.',
            'class_id.exists' => 'The selected class is invalid.',
            'name.required' => 'The section name is required.',
            'name.max' => 'The section name must not exceed 100 characters.',
            'capacity.integer' => 'The capacity must be a whole number.',
            'capacity.min' => 'The capacity must be at least 1.',
            'class_teacher_id.exists' => 'The selected class teacher is invalid.',
            'status.required' => 'The status is required.',
            'status.in' => 'Please select a valid status (active or inactive).',
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
            'class_id' => 'class',
            'class_teacher_id' => 'class teacher',
        ];
    }
}
