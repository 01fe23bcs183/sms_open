<?php

namespace App\Http\Requests;

/**
 * Store Attendance Request
 * 
 * Prompt 297: Standardize Validation Errors for Web and JSON
 * 
 * Validates attendance marking requests.
 */
class StoreAttendanceRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'before_or_equal:today'],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            
            // Attendance records array
            'attendance' => ['required', 'array', 'min:1'],
            'attendance.*.student_id' => ['required', 'exists:students,id'],
            'attendance.*.attendance_type_id' => ['required', 'exists:attendance_types,id'],
            'attendance.*.remarks' => ['nullable', 'string', 'max:500'],
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
            'date.before_or_equal' => 'Attendance cannot be marked for future dates.',
            'attendance.required' => 'Please provide attendance records.',
            'attendance.min' => 'At least one attendance record is required.',
            'attendance.*.student_id.required' => 'Student ID is required for each attendance record.',
            'attendance.*.student_id.exists' => 'Invalid student selected.',
            'attendance.*.attendance_type_id.required' => 'Attendance type is required for each record.',
            'attendance.*.attendance_type_id.exists' => 'Invalid attendance type selected.',
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
            'attendance.*.student_id' => 'student',
            'attendance.*.attendance_type_id' => 'attendance type',
            'attendance.*.remarks' => 'remarks',
        ];
    }
}
