<?php

namespace App\Http\Requests;

/**
 * Exam Store Request
 * 
 * Prompt 345: Create Exam Store Form Request
 * 
 * Validates exam creation form data.
 */
class ExamStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-exams');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Exam Information
            'name' => ['required', 'string', 'max:255'],
            'exam_type_id' => ['required', 'exists:exam_types,id'],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'class_id' => ['required', 'exists:classes,id'],
            
            // Sections
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['nullable', 'exists:sections,id'],
            
            // Dates
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'result_date' => ['nullable', 'date', 'after:end_date'],
            
            // Marks
            'passing_marks' => ['required', 'numeric', 'min:0'],
            'total_marks' => ['required', 'numeric', 'min:0'],
            
            // Additional Information
            'remarks' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,completed'],
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
            'name.required' => 'The exam name is required.',
            'name.max' => 'The exam name must not exceed 255 characters.',
            'exam_type_id.required' => 'Please select an exam type.',
            'exam_type_id.exists' => 'The selected exam type is invalid.',
            'academic_session_id.required' => 'Please select an academic session.',
            'academic_session_id.exists' => 'The selected academic session is invalid.',
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'The selected class is invalid.',
            'section_ids.array' => 'The sections must be an array.',
            'section_ids.*.exists' => 'One or more selected sections are invalid.',
            'start_date.required' => 'The start date is required.',
            'start_date.date' => 'Please enter a valid start date.',
            'end_date.required' => 'The end date is required.',
            'end_date.date' => 'Please enter a valid end date.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
            'result_date.date' => 'Please enter a valid result date.',
            'result_date.after' => 'The result date must be after the end date.',
            'passing_marks.required' => 'The passing marks is required.',
            'passing_marks.numeric' => 'The passing marks must be a number.',
            'passing_marks.min' => 'The passing marks cannot be negative.',
            'total_marks.required' => 'The total marks is required.',
            'total_marks.numeric' => 'The total marks must be a number.',
            'total_marks.min' => 'The total marks cannot be negative.',
            'status.required' => 'The status is required.',
            'status.in' => 'Please select a valid status (draft, published, or completed).',
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
            'exam_type_id' => 'exam type',
            'academic_session_id' => 'academic session',
            'class_id' => 'class',
            'section_ids' => 'sections',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'result_date' => 'result date',
            'passing_marks' => 'passing marks',
            'total_marks' => 'total marks',
        ];
    }
}
