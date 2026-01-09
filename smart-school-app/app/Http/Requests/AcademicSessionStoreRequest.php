<?php

namespace App\Http\Requests;

/**
 * Academic Session Store Request
 * 
 * Prompt 358: Create Academic Session Store Form Request
 * 
 * Validates academic session creation data.
 */
class AcademicSessionStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-academic-sessions');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Session Information
            'name' => ['required', 'string', 'max:100', 'unique:academic_sessions,name'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            
            // Current Session Flag
            'is_current' => ['nullable', 'boolean'],
            
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
            'name.required' => 'The session name is required.',
            'name.max' => 'The session name must not exceed 100 characters.',
            'name.unique' => 'This session name already exists.',
            'start_date.required' => 'The start date is required.',
            'start_date.date' => 'Please enter a valid start date.',
            'end_date.required' => 'The end date is required.',
            'end_date.date' => 'Please enter a valid end date.',
            'end_date.after' => 'The end date must be after the start date.',
            'is_current.boolean' => 'The current session flag must be true or false.',
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
            'start_date' => 'start date',
            'end_date' => 'end date',
            'is_current' => 'current session',
        ];
    }
}
