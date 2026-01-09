<?php

namespace App\Http\Requests;

/**
 * Transport Route Store Request
 * 
 * Prompt 350: Create Transport Route Store Form Request
 * 
 * Validates transport route creation form data.
 */
class TransportRouteStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-transport-routes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Route Information
            'name' => ['required', 'string', 'max:255'],
            'route_number' => ['required', 'string', 'max:50', 'unique:transport_routes,route_number'],
            'start_point' => ['required', 'string', 'max:255'],
            'end_point' => ['required', 'string', 'max:255'],
            
            // Distance and Fare
            'distance' => ['nullable', 'numeric', 'min:0'],
            'fare' => ['required', 'numeric', 'min:0'],
            
            // Vehicle and Driver
            'vehicle_id' => ['required', 'exists:transport_vehicles,id'],
            'driver_id' => ['required', 'exists:users,id'],
            
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
            'name.required' => 'The route name is required.',
            'name.max' => 'The route name must not exceed 255 characters.',
            'route_number.required' => 'The route number is required.',
            'route_number.max' => 'The route number must not exceed 50 characters.',
            'route_number.unique' => 'This route number is already assigned to another route.',
            'start_point.required' => 'The start point is required.',
            'start_point.max' => 'The start point must not exceed 255 characters.',
            'end_point.required' => 'The end point is required.',
            'end_point.max' => 'The end point must not exceed 255 characters.',
            'distance.numeric' => 'The distance must be a number.',
            'distance.min' => 'The distance cannot be negative.',
            'fare.required' => 'The fare is required.',
            'fare.numeric' => 'The fare must be a number.',
            'fare.min' => 'The fare cannot be negative.',
            'vehicle_id.required' => 'Please select a vehicle.',
            'vehicle_id.exists' => 'The selected vehicle is invalid.',
            'driver_id.required' => 'Please select a driver.',
            'driver_id.exists' => 'The selected driver is invalid.',
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
            'route_number' => 'route number',
            'start_point' => 'start point',
            'end_point' => 'end point',
            'vehicle_id' => 'vehicle',
            'driver_id' => 'driver',
        ];
    }
}
