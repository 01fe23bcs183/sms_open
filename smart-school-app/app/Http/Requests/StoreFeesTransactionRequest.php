<?php

namespace App\Http\Requests;

/**
 * Store Fees Transaction Request
 * 
 * Prompt 297: Standardize Validation Errors for Web and JSON
 * 
 * Validates fee payment requests.
 */
class StoreFeesTransactionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'student_id' => ['required', 'exists:students,id'],
            'fees_allotment_id' => ['required', 'exists:fees_allotments,id'],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'fine_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,cheque,dd,online,upi,card'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];

        // Add conditional rules based on payment method
        if ($this->input('payment_method') === 'cheque') {
            $rules['cheque_number'] = ['required', 'string', 'max:50'];
            $rules['cheque_date'] = ['required', 'date'];
            $rules['bank_name'] = ['required', 'string', 'max:255'];
        }

        if ($this->input('payment_method') === 'dd') {
            $rules['dd_number'] = ['required', 'string', 'max:50'];
            $rules['dd_date'] = ['required', 'date'];
            $rules['bank_name'] = ['required', 'string', 'max:255'];
        }

        if (in_array($this->input('payment_method'), ['online', 'upi', 'card'])) {
            $rules['transaction_id'] = ['required', 'string', 'max:100'];
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    protected function customMessages(): array
    {
        return [
            'amount.min' => 'Payment amount must be greater than zero.',
            'transaction_date.before_or_equal' => 'Transaction date cannot be in the future.',
            'cheque_number.required' => 'Cheque number is required for cheque payments.',
            'cheque_date.required' => 'Cheque date is required for cheque payments.',
            'dd_number.required' => 'DD number is required for demand draft payments.',
            'dd_date.required' => 'DD date is required for demand draft payments.',
            'transaction_id.required' => 'Transaction ID is required for online/UPI/card payments.',
            'bank_name.required' => 'Bank name is required for cheque/DD payments.',
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
            'fees_allotment_id' => 'fee allotment',
            'dd_number' => 'DD number',
            'dd_date' => 'DD date',
        ];
    }
}
