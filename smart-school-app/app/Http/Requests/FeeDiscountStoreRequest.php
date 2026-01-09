<?php

namespace App\Http\Requests;

/**
 * Fee Discount Store Request
 * 
 * Prompt 366: Create Fee Discount Store Form Request
 * 
 * Validates fee discount creation data.
 */
class FeeDiscountStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-fee-discounts');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Discount Information
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            
            // Date Range
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            
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
            'name.required' => 'The discount name is required.',
            'name.max' => 'The discount name must not exceed 100 characters.',
            'type.required' => 'The discount type is required.',
            'type.in' => 'Please select a valid discount type (percentage or fixed).',
            'value.required' => 'The discount value is required.',
            'value.numeric' => 'The discount value must be a number.',
            'value.min' => 'The discount value must be at least 0.',
            'start_date.date' => 'Please enter a valid start date.',
            'end_date.date' => 'Please enter a valid end date.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
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
        ];
    }
}
