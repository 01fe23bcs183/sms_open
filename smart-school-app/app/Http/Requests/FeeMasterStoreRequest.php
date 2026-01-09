<?php

namespace App\Http\Requests;

/**
 * Fee Master Store Request
 * 
 * Prompt 365: Create Fee Master Store Form Request
 * 
 * Validates fee master creation data.
 */
class FeeMasterStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-fee-masters');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Fee Master Information
            'fee_group_id' => ['required', 'exists:fees_groups,id'],
            'fee_type_id' => ['required', 'exists:fees_types,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            
            // Optional Flag
            'is_optional' => ['nullable', 'boolean'],
            
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
            'fee_group_id.required' => 'The fee group is required.',
            'fee_group_id.exists' => 'The selected fee group is invalid.',
            'fee_type_id.required' => 'The fee type is required.',
            'fee_type_id.exists' => 'The selected fee type is invalid.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least 0.',
            'is_optional.boolean' => 'The optional flag must be true or false.',
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
            'fee_group_id' => 'fee group',
            'fee_type_id' => 'fee type',
            'is_optional' => 'optional',
        ];
    }
}
