<?php

namespace App\Http\Requests;

/**
 * Subject Store Request
 * 
 * Prompt 360: Create Subject Store Form Request
 * 
 * Validates subject creation data.
 */
class SubjectStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-subjects');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Subject Information
            'class_id' => ['required', 'exists:classes,id'],
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50', 'unique:subjects,code'],
            'type' => ['required', 'in:theory,practical'],
            
            // Marks
            'full_marks' => ['required', 'numeric', 'min:0'],
            'pass_marks' => ['required', 'numeric', 'min:0', 'lte:full_marks'],
            
            // Teacher
            'teacher_id' => ['nullable', 'exists:users,id'],
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
            'name.required' => 'The subject name is required.',
            'name.max' => 'The subject name must not exceed 150 characters.',
            'code.required' => 'The subject code is required.',
            'code.max' => 'The subject code must not exceed 50 characters.',
            'code.unique' => 'This subject code already exists.',
            'type.required' => 'The subject type is required.',
            'type.in' => 'Please select a valid subject type (theory or practical).',
            'full_marks.required' => 'The full marks is required.',
            'full_marks.numeric' => 'The full marks must be a number.',
            'full_marks.min' => 'The full marks must be at least 0.',
            'pass_marks.required' => 'The pass marks is required.',
            'pass_marks.numeric' => 'The pass marks must be a number.',
            'pass_marks.min' => 'The pass marks must be at least 0.',
            'pass_marks.lte' => 'The pass marks must be less than or equal to full marks.',
            'teacher_id.exists' => 'The selected teacher is invalid.',
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
            'full_marks' => 'full marks',
            'pass_marks' => 'pass marks',
            'teacher_id' => 'teacher',
        ];
    }
}
