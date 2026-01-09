<?php

namespace App\Http\Requests;

/**
 * Fee Type Store Request
 * 
 * Prompt 363: Create Fee Type Store Form Request
 * 
 * Validates fee type creation data.
 */
class FeeTypeStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-fee-types');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Fee Type Information
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'unique:fees_types,code'],
            'description' => ['nullable', 'string', 'max:500'],
            
            // Active Status
            'is_active' => ['nullable', 'boolean'],
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
            'name.required' => 'The fee type name is required.',
            'name.max' => 'The fee type name must not exceed 100 characters.',
            'code.required' => 'The fee type code is required.',
            'code.max' => 'The fee type code must not exceed 50 characters.',
            'code.unique' => 'This fee type code already exists.',
            'description.max' => 'The description must not exceed 500 characters.',
            'is_active.boolean' => 'The active status must be true or false.',
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
            'is_active' => 'active status',
        ];
    }
}
