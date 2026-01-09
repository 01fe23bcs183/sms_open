<?php

namespace App\Http\Requests;

/**
 * Expense Store Request
 * 
 * Prompt 357: Create Expense Store Form Request
 * 
 * Validates expense entry form data.
 */
class ExpenseStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-expenses');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Expense Information
            'expense_type_id' => ['required', 'exists:expense_types,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'description' => ['required', 'string'],
            
            // Reference
            'reference_number' => ['nullable', 'string', 'max:100'],
            
            // Attachment
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
            
            // Remarks
            'remarks' => ['nullable', 'string', 'max:255'],
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
            'expense_type_id.required' => 'The expense type is required.',
            'expense_type_id.exists' => 'The selected expense type is invalid.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least 0.',
            'date.required' => 'The date is required.',
            'date.date' => 'Please enter a valid date.',
            'description.required' => 'The description is required.',
            'reference_number.max' => 'The reference number must not exceed 100 characters.',
            'attachment.file' => 'The attachment must be a file.',
            'attachment.mimes' => 'The attachment must be a file of type: pdf, doc, docx, jpg, jpeg, png.',
            'attachment.max' => 'The attachment size must not exceed 5MB.',
            'remarks.max' => 'The remarks must not exceed 255 characters.',
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
            'expense_type_id' => 'expense type',
            'reference_number' => 'reference number',
        ];
    }
}
