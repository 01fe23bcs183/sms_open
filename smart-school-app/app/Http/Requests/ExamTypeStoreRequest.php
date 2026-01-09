<?php

namespace App\Http\Requests;

/**
 * Exam Type Store Request
 * 
 * Prompt 361: Create Exam Type Store Form Request
 * 
 * Validates exam type creation data.
 */
class ExamTypeStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-exam-types');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Exam Type Information
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:exam_types,code'],
            'description' => ['nullable', 'string', 'max:500'],
            
            // Active Status
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
            'name.required' => 'The exam type name is required.',
            'name.max' => 'The exam type name must not exceed 100 characters.',
            'code.required' => 'The exam type code is required.',
            'code.max' => 'The exam type code must not exceed 20 characters.',
            'code.unique' => 'This exam type code already exists.',
            'description.max' => 'The description must not exceed 500 characters.',
            'is_active.boolean' => 'The active status must be true or false.',
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
            'is_active' => 'active status',
        ];
    }
}
