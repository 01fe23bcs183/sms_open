<?php

namespace App\Http\Requests;

/**
 * Exam Schedule Store Request
 * 
 * Prompt 362: Create Exam Schedule Store Form Request
 * 
 * Validates exam schedule creation data.
 * Note: The specification mentions GradeScaleStoreRequest for Prompt 362,
 * but the session task list specifies ExamScheduleStoreRequest.
 * Following the session task list as the authoritative source for this session.
 */
class ExamScheduleStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-exam-schedules');
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
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            
            // Schedule Details
            'exam_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room_number' => ['nullable', 'string', 'max:50'],
            
            // Marks
            'full_marks' => ['required', 'numeric', 'min:0'],
            'passing_marks' => ['required', 'numeric', 'min:0', 'lte:full_marks'],
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
            'exam_id.required' => 'The exam is required.',
            'exam_id.exists' => 'The selected exam is invalid.',
            'class_id.required' => 'The class is required.',
            'class_id.exists' => 'The selected class is invalid.',
            'section_id.required' => 'The section is required.',
            'section_id.exists' => 'The selected section is invalid.',
            'subject_id.required' => 'The subject is required.',
            'subject_id.exists' => 'The selected subject is invalid.',
            'exam_date.required' => 'The exam date is required.',
            'exam_date.date' => 'Please enter a valid exam date.',
            'start_time.required' => 'The start time is required.',
            'start_time.date_format' => 'The start time must be in HH:MM format.',
            'end_time.required' => 'The end time is required.',
            'end_time.date_format' => 'The end time must be in HH:MM format.',
            'end_time.after' => 'The end time must be after the start time.',
            'room_number.max' => 'The room number must not exceed 50 characters.',
            'full_marks.required' => 'The full marks is required.',
            'full_marks.numeric' => 'The full marks must be a number.',
            'full_marks.min' => 'The full marks must be at least 0.',
            'passing_marks.required' => 'The passing marks is required.',
            'passing_marks.numeric' => 'The passing marks must be a number.',
            'passing_marks.min' => 'The passing marks must be at least 0.',
            'passing_marks.lte' => 'The passing marks must be less than or equal to full marks.',
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
            'class_id' => 'class',
            'section_id' => 'section',
            'subject_id' => 'subject',
            'exam_date' => 'exam date',
            'start_time' => 'start time',
            'end_time' => 'end time',
            'room_number' => 'room number',
            'full_marks' => 'full marks',
            'passing_marks' => 'passing marks',
        ];
    }
}
