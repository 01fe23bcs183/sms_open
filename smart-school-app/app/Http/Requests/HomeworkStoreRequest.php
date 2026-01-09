<?php

namespace App\Http\Requests;

/**
 * Homework Store Request
 * 
 * Prompt 354: Create Homework Store Form Request
 * 
 * Validates homework creation form data.
 */
class HomeworkStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-homework');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Homework Information
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            
            // Class and Section
            'class_id' => ['required', 'exists:classes,id'],
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['nullable', 'exists:sections,id'],
            
            // Subject and Teacher
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:users,id'],
            
            // Dates
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'submission_date' => ['nullable', 'date', 'after_or_equal:due_date'],
            
            // Marks
            'marks' => ['nullable', 'numeric', 'min:0'],
            
            // Attachment
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
            
            // Status
            'status' => ['required', 'in:draft,published,closed'],
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
            'title.required' => 'The homework title is required.',
            'title.max' => 'The homework title must not exceed 255 characters.',
            'description.required' => 'The homework description is required.',
            'class_id.required' => 'The class is required.',
            'class_id.exists' => 'The selected class is invalid.',
            'section_ids.array' => 'The sections must be an array.',
            'section_ids.*.exists' => 'One or more selected sections are invalid.',
            'subject_id.required' => 'The subject is required.',
            'subject_id.exists' => 'The selected subject is invalid.',
            'teacher_id.required' => 'The teacher is required.',
            'teacher_id.exists' => 'The selected teacher is invalid.',
            'due_date.required' => 'The due date is required.',
            'due_date.date' => 'Please enter a valid due date.',
            'due_date.after_or_equal' => 'The due date must be today or a future date.',
            'submission_date.date' => 'Please enter a valid submission date.',
            'submission_date.after_or_equal' => 'The submission date must be on or after the due date.',
            'marks.numeric' => 'The marks must be a number.',
            'marks.min' => 'The marks must be at least 0.',
            'attachment.file' => 'The attachment must be a file.',
            'attachment.mimes' => 'The attachment must be a file of type: pdf, doc, docx, jpg, jpeg, png.',
            'attachment.max' => 'The attachment size must not exceed 5MB.',
            'status.required' => 'The status is required.',
            'status.in' => 'Please select a valid status (draft, published, or closed).',
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
            'section_ids' => 'sections',
            'subject_id' => 'subject',
            'teacher_id' => 'teacher',
            'due_date' => 'due date',
            'submission_date' => 'submission date',
        ];
    }
}
