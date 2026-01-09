<?php

namespace App\Http\Requests;

/**
 * Income Store Request
 * 
 * Prompt 356: Create Income Store Form Request
 * 
 * Validates income entry form data.
 */
class IncomeStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-income');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Income Information
            'income_type_id' => ['required', 'exists:income_types,id'],
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
            'income_type_id.required' => 'The income type is required.',
            'income_type_id.exists' => 'The selected income type is invalid.',
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
            'income_type_id' => 'income type',
            'reference_number' => 'reference number',
        ];
    }
}
