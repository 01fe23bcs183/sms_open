<?php

namespace App\Http\Requests;

/**
 * Fee Group Store Request
 * 
 * Prompt 364: Create Fee Group Store Form Request
 * 
 * Validates fee group creation data.
 */
class FeeGroupStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-fee-groups');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Fee Group Information
            'name' => ['required', 'string', 'max:100'],
            'due_date' => ['required', 'date'],
            
            // Fine Rule
            'fine_rule_id' => ['nullable', 'exists:fees_fines,id'],
            
            // Description
            'description' => ['nullable', 'string', 'max:500'],
            
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
            'name.required' => 'The fee group name is required.',
            'name.max' => 'The fee group name must not exceed 100 characters.',
            'due_date.required' => 'The due date is required.',
            'due_date.date' => 'Please enter a valid due date.',
            'fine_rule_id.exists' => 'The selected fine rule is invalid.',
            'description.max' => 'The description must not exceed 500 characters.',
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
            'due_date' => 'due date',
            'fine_rule_id' => 'fine rule',
        ];
    }
}
