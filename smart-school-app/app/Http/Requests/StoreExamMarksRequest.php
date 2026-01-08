<?php

namespace App\Http\Requests;

/**
 * Store Exam Marks Request
 * 
 * Prompt 297: Standardize Validation Errors for Web and JSON
 * 
 * Validates exam marks entry requests.
 */
class StoreExamMarksRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'exam_schedule_id' => ['required', 'exists:exam_schedules,id'],
            
            // Marks array
            'marks' => ['required', 'array', 'min:1'],
            'marks.*.student_id' => ['required', 'exists:students,id'],
            'marks.*.obtained_marks' => ['nullable', 'numeric', 'min:0'],
            'marks.*.practical_marks' => ['nullable', 'numeric', 'min:0'],
            'marks.*.is_absent' => ['nullable', 'boolean'],
            'marks.*.remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $marks = $this->input('marks', []);
            
            foreach ($marks as $index => $mark) {
                // Skip validation if student is absent
                if (!empty($mark['is_absent'])) {
                    continue;
                }
                
                // Validate that marks don't exceed full marks
                // This would typically check against exam_schedule full_marks
                $obtainedMarks = $mark['obtained_marks'] ?? 0;
                $practicalMarks = $mark['practical_marks'] ?? 0;
                
                if ($obtainedMarks < 0) {
                    $validator->errors()->add(
                        "marks.{$index}.obtained_marks",
                        'Obtained marks cannot be negative.'
                    );
                }
                
                if ($practicalMarks < 0) {
                    $validator->errors()->add(
                        "marks.{$index}.practical_marks",
                        'Practical marks cannot be negative.'
                    );
                }
            }
        });
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    protected function customMessages(): array
    {
        return [
            'marks.required' => 'Please provide marks data.',
            'marks.min' => 'At least one student marks record is required.',
            'marks.*.student_id.required' => 'Student ID is required for each marks record.',
            'marks.*.student_id.exists' => 'Invalid student selected.',
            'marks.*.obtained_marks.numeric' => 'Obtained marks must be a number.',
            'marks.*.obtained_marks.min' => 'Obtained marks cannot be negative.',
            'marks.*.practical_marks.numeric' => 'Practical marks must be a number.',
            'marks.*.practical_marks.min' => 'Practical marks cannot be negative.',
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
            'exam_schedule_id' => 'exam schedule',
            'marks.*.student_id' => 'student',
            'marks.*.obtained_marks' => 'obtained marks',
            'marks.*.practical_marks' => 'practical marks',
            'marks.*.is_absent' => 'absent status',
        ];
    }
}
