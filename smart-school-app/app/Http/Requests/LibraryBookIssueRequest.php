<?php

namespace App\Http\Requests;

/**
 * Library Book Issue Request
 * 
 * Prompt 349: Create Library Book Issue Form Request
 * 
 * Validates library book issue form data.
 */
class LibraryBookIssueRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-library-issues');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Book and Member Information
            'book_id' => ['required', 'exists:library_books,id'],
            'member_id' => ['required', 'exists:library_members,id'],
            
            // Issue Information
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after:issue_date'],
            
            // Additional Information
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
            'book_id.required' => 'Please select a book.',
            'book_id.exists' => 'The selected book is invalid.',
            'member_id.required' => 'Please select a member.',
            'member_id.exists' => 'The selected member is invalid.',
            'issue_date.required' => 'The issue date is required.',
            'issue_date.date' => 'Please enter a valid issue date.',
            'due_date.required' => 'The due date is required.',
            'due_date.date' => 'Please enter a valid due date.',
            'due_date.after' => 'The due date must be after the issue date.',
            'remarks.max' => 'Remarks must not exceed 255 characters.',
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
            'book_id' => 'book',
            'member_id' => 'member',
            'issue_date' => 'issue date',
            'due_date' => 'due date',
        ];
    }
}
