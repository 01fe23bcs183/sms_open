<?php

namespace App\Http\Requests;

/**
 * Attendance Store Request
 * 
 * Prompt 344: Create Attendance Store Form Request
 * 
 * Validates attendance marking form data.
 */
class AttendanceStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-attendance');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Attendance Information
            'date' => ['required', 'date'],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:users,id'],
            'attendance_type' => ['required', 'in:daily,subject'],
            
            // Student Attendance Records
            'attendance' => ['required', 'array'],
            'attendance.*.student_id' => ['required', 'exists:students,id'],
            'attendance.*.status' => ['required', 'in:present,absent,late,half_day'],
            'attendance.*.remarks' => ['nullable', 'string', 'max:255'],
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
            'date.required' => 'The attendance date is required.',
            'date.date' => 'Please enter a valid date.',
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'The selected class is invalid.',
            'section_id.required' => 'Please select a section.',
            'section_id.exists' => 'The selected section is invalid.',
            'subject_id.exists' => 'The selected subject is invalid.',
            'teacher_id.required' => 'Please select a teacher.',
            'teacher_id.exists' => 'The selected teacher is invalid.',
            'attendance_type.required' => 'The attendance type is required.',
            'attendance_type.in' => 'Please select a valid attendance type (daily or subject).',
            'attendance.required' => 'Attendance records are required.',
            'attendance.array' => 'Attendance records must be an array.',
            'attendance.*.student_id.required' => 'Student ID is required for each attendance record.',
            'attendance.*.student_id.exists' => 'One or more students are invalid.',
            'attendance.*.status.required' => 'Attendance status is required for each student.',
            'attendance.*.status.in' => 'Please select a valid attendance status (present, absent, late, or half_day).',
            'attendance.*.remarks.max' => 'Remarks must not exceed 255 characters.',
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
            'section_id' => 'section',
            'subject_id' => 'subject',
            'teacher_id' => 'teacher',
            'attendance_type' => 'attendance type',
            'attendance.*.student_id' => 'student',
            'attendance.*.status' => 'attendance status',
        ];
    }
}
