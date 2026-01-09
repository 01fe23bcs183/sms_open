<?php

namespace App\Http\Requests;

/**
 * Exam Mark Store Request
 * 
 * Prompt 346: Create Exam Mark Store Form Request
 * 
 * Validates exam marks entry form data.
 */
class ExamMarkStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-exam-marks');
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
            'exam_id' => ['required', 'exists:exams,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            
            // Student Marks
            'marks' => ['required', 'array'],
            'marks.*.student_id' => ['required', 'exists:students,id'],
            'marks.*.obtained_marks' => ['required', 'numeric', 'min:0'],
            'marks.*.remarks' => ['nullable', 'string', 'max:255'],
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
            'exam_id.required' => 'Please select an exam.',
            'exam_id.exists' => 'The selected exam is invalid.',
            'subject_id.required' => 'Please select a subject.',
            'subject_id.exists' => 'The selected subject is invalid.',
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'The selected class is invalid.',
            'section_id.required' => 'Please select a section.',
            'section_id.exists' => 'The selected section is invalid.',
            'marks.required' => 'Marks records are required.',
            'marks.array' => 'Marks records must be an array.',
            'marks.*.student_id.required' => 'Student ID is required for each marks record.',
            'marks.*.student_id.exists' => 'One or more students are invalid.',
            'marks.*.obtained_marks.required' => 'Obtained marks is required for each student.',
            'marks.*.obtained_marks.numeric' => 'Obtained marks must be a number.',
            'marks.*.obtained_marks.min' => 'Obtained marks cannot be negative.',
            'marks.*.remarks.max' => 'Remarks must not exceed 255 characters.',
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
            'exam_id' => 'exam',
            'subject_id' => 'subject',
            'class_id' => 'class',
            'section_id' => 'section',
            'marks.*.student_id' => 'student',
            'marks.*.obtained_marks' => 'obtained marks',
        ];
    }
}
