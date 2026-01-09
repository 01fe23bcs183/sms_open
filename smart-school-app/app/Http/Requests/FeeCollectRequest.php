<?php

namespace App\Http\Requests;

/**
 * Fee Collect Request
 * 
 * Prompt 347: Create Fee Collect Form Request
 * 
 * Validates fee collection form data.
 */
class FeeCollectRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('collect-fees');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Student and Fee Information
            'student_id' => ['required', 'exists:students,id'],
            'fee_ids' => ['required', 'array'],
            'fee_ids.*' => ['required', 'exists:fees_allotments,id'],
            
            // Payment Information
            'payment_method' => ['required', 'in:cash,cheque,dd,online,razorpay,stripe,paypal'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['required', 'date'],
            
            // Reference Information
            'reference_number' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'cheque_date' => ['nullable', 'date', 'required_if:payment_method,cheque'],
            
            // Additional Information
            'remarks' => ['nullable', 'string', 'max:255'],
            
            // Discount Information
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_reason' => ['nullable', 'string', 'max:255'],
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
            'student_id.required' => 'Please select a student.',
            'student_id.exists' => 'The selected student is invalid.',
            'fee_ids.required' => 'Please select at least one fee.',
            'fee_ids.array' => 'Fee IDs must be an array.',
            'fee_ids.*.required' => 'Fee ID is required.',
            'fee_ids.*.exists' => 'One or more selected fees are invalid.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Please select a valid payment method.',
            'amount.required' => 'The payment amount is required.',
            'amount.numeric' => 'The payment amount must be a number.',
            'amount.min' => 'The payment amount cannot be negative.',
            'payment_date.required' => 'The payment date is required.',
            'payment_date.date' => 'Please enter a valid payment date.',
            'reference_number.max' => 'The reference number must not exceed 100 characters.',
            'bank_name.max' => 'The bank name must not exceed 100 characters.',
            'cheque_date.date' => 'Please enter a valid cheque date.',
            'cheque_date.required_if' => 'The cheque date is required when payment method is cheque.',
            'remarks.max' => 'Remarks must not exceed 255 characters.',
            'discount_amount.numeric' => 'The discount amount must be a number.',
            'discount_amount.min' => 'The discount amount cannot be negative.',
            'discount_reason.max' => 'The discount reason must not exceed 255 characters.',
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
            'fee_ids' => 'fees',
            'payment_method' => 'payment method',
            'payment_date' => 'payment date',
            'reference_number' => 'reference number',
            'bank_name' => 'bank name',
            'cheque_date' => 'cheque date',
            'discount_amount' => 'discount amount',
            'discount_reason' => 'discount reason',
        ];
    }
}
