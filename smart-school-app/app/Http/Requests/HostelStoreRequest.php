<?php

namespace App\Http\Requests;

/**
 * Hostel Store Request
 * 
 * Prompt 351: Create Hostel Store Form Request
 * 
 * Validates hostel creation form data.
 */
class HostelStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-hostels');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Hostel Information
            'name' => ['required', 'string', 'max:255'],
            'hostel_type' => ['required', 'in:boys,girls,co-ed'],
            
            // Address Information
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'zip_code' => ['required', 'string', 'max:20'],
            
            // Contact Information
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            
            // Capacity and Management
            'capacity' => ['required', 'numeric', 'min:1'],
            'warden_id' => ['required', 'exists:users,id'],
            
            // Additional Information
            'description' => ['nullable', 'string'],
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
            'name.required' => 'The hostel name is required.',
            'name.max' => 'The hostel name must not exceed 255 characters.',
            'hostel_type.required' => 'The hostel type is required.',
            'hostel_type.in' => 'Please select a valid hostel type (boys, girls, or co-ed).',
            'address.required' => 'The address is required.',
            'city.required' => 'The city is required.',
            'city.max' => 'The city must not exceed 100 characters.',
            'state.required' => 'The state is required.',
            'state.max' => 'The state must not exceed 100 characters.',
            'country.required' => 'The country is required.',
            'country.max' => 'The country must not exceed 100 characters.',
            'zip_code.required' => 'The zip code is required.',
            'zip_code.max' => 'The zip code must not exceed 20 characters.',
            'phone.required' => 'The phone number is required.',
            'phone.max' => 'The phone number must not exceed 20 characters.',
            'email.email' => 'Please enter a valid email address.',
            'capacity.required' => 'The capacity is required.',
            'capacity.numeric' => 'The capacity must be a number.',
            'capacity.min' => 'The capacity must be at least 1.',
            'warden_id.required' => 'Please select a warden.',
            'warden_id.exists' => 'The selected warden is invalid.',
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
            'hostel_type' => 'hostel type',
            'zip_code' => 'zip code',
            'warden_id' => 'warden',
        ];
    }
}
