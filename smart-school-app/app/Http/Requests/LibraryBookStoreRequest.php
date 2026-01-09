<?php

namespace App\Http\Requests;

/**
 * Library Book Store Request
 * 
 * Prompt 348: Create Library Book Store Form Request
 * 
 * Validates library book creation form data.
 */
class LibraryBookStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-library-books');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Book Information
            'title' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:50', 'unique:library_books,isbn'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'exists:library_categories,id'],
            'publication_date' => ['nullable', 'date'],
            
            // Pricing and Quantity
            'price' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:1'],
            'available_quantity' => ['required', 'numeric', 'min:0', 'lte:quantity'],
            
            // Location Information
            'shelf_number' => ['nullable', 'string', 'max:50'],
            'rack_number' => ['nullable', 'string', 'max:50'],
            
            // Additional Information
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
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
            'title.required' => 'The book title is required.',
            'title.max' => 'The book title must not exceed 255 characters.',
            'isbn.max' => 'The ISBN must not exceed 50 characters.',
            'isbn.unique' => 'This ISBN is already registered.',
            'author.required' => 'The author name is required.',
            'author.max' => 'The author name must not exceed 255 characters.',
            'publisher.max' => 'The publisher name must not exceed 255 characters.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category is invalid.',
            'publication_date.date' => 'Please enter a valid publication date.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price cannot be negative.',
            'quantity.required' => 'The quantity is required.',
            'quantity.numeric' => 'The quantity must be a number.',
            'quantity.min' => 'The quantity must be at least 1.',
            'available_quantity.required' => 'The available quantity is required.',
            'available_quantity.numeric' => 'The available quantity must be a number.',
            'available_quantity.min' => 'The available quantity cannot be negative.',
            'available_quantity.lte' => 'The available quantity cannot exceed the total quantity.',
            'shelf_number.max' => 'The shelf number must not exceed 50 characters.',
            'rack_number.max' => 'The rack number must not exceed 50 characters.',
            'cover_image.image' => 'The cover image must be an image.',
            'cover_image.mimes' => 'The cover image must be a file of type: jpeg, png, jpg, gif, svg.',
            'cover_image.max' => 'The cover image size must not exceed 2MB.',
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
            'category_id' => 'category',
            'publication_date' => 'publication date',
            'available_quantity' => 'available quantity',
            'shelf_number' => 'shelf number',
            'rack_number' => 'rack number',
            'cover_image' => 'cover image',
        ];
    }
}
