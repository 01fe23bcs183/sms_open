<?php

namespace App\Http\Requests;

/**
 * Fee Allotment Store Request
 * 
 * Prompt 367: Create Fee Allotment Store Form Request
 * 
 * Validates fee allotment data.
 */
class FeeAllotmentStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-fee-allotments');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Student or Class (one is required)
            'student_id' => ['required_without:class_id', 'nullable', 'exists:students,id'],
            'class_id' => ['required_without:student_id', 'nullable', 'exists:classes,id'],
            
            // Academic Session
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            
            // Fee Group
            'fee_group_id' => ['required', 'exists:fees_groups,id'],
            
            // Discount
            'discount_id' => ['nullable', 'exists:fees_discounts,id'],
            
            // Due Date
            'due_date' => ['required', 'date'],
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
            'student_id.required_without' => 'Either a student or a class must be selected.',
            'student_id.exists' => 'The selected student is invalid.',
            'class_id.required_without' => 'Either a student or a class must be selected.',
            'class_id.exists' => 'The selected class is invalid.',
            'academic_session_id.required' => 'The academic session is required.',
            'academic_session_id.exists' => 'The selected academic session is invalid.',
            'fee_group_id.required' => 'The fee group is required.',
            'fee_group_id.exists' => 'The selected fee group is invalid.',
            'discount_id.exists' => 'The selected discount is invalid.',
            'due_date.required' => 'The due date is required.',
            'due_date.date' => 'Please enter a valid due date.',
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
            'student_id' => 'student',
            'class_id' => 'class',
            'academic_session_id' => 'academic session',
            'fee_group_id' => 'fee group',
            'discount_id' => 'discount',
            'due_date' => 'due date',
        ];
    }
}
