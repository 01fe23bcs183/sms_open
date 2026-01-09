<?php

namespace App\Http\Requests;

/**
 * Notice Store Request
 * 
 * Prompt 352: Create Notice Store Form Request
 * 
 * Validates notice creation form data.
 */
class NoticeStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-notices');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Notice Information
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'notice_type' => ['required', 'in:general,exam,holiday,emergency,other'],
            
            // Target Audience
            'target_audience' => ['required', 'in:all,students,teachers,parents,staff'],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['nullable', 'exists:classes,id'],
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['nullable', 'exists:sections,id'],
            
            // Dates
            'publish_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:publish_date'],
            
            // Attachment
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            
            // Status
            'status' => ['required', 'in:draft,published,archived'],
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
            'title.required' => 'The notice title is required.',
            'title.max' => 'The notice title must not exceed 255 characters.',
            'content.required' => 'The notice content is required.',
            'notice_type.required' => 'The notice type is required.',
            'notice_type.in' => 'Please select a valid notice type (general, exam, holiday, emergency, or other).',
            'target_audience.required' => 'The target audience is required.',
            'target_audience.in' => 'Please select a valid target audience (all, students, teachers, parents, or staff).',
            'class_ids.array' => 'The classes must be an array.',
            'class_ids.*.exists' => 'One or more selected classes are invalid.',
            'section_ids.array' => 'The sections must be an array.',
            'section_ids.*.exists' => 'One or more selected sections are invalid.',
            'publish_date.required' => 'The publish date is required.',
            'publish_date.date' => 'Please enter a valid publish date.',
            'expiry_date.date' => 'Please enter a valid expiry date.',
            'expiry_date.after_or_equal' => 'The expiry date must be on or after the publish date.',
            'attachment.file' => 'The attachment must be a file.',
            'attachment.mimes' => 'The attachment must be a file of type: pdf, doc, docx.',
            'attachment.max' => 'The attachment size must not exceed 5MB.',
            'status.required' => 'The status is required.',
            'status.in' => 'Please select a valid status (draft, published, or archived).',
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
            'notice_type' => 'notice type',
            'target_audience' => 'target audience',
            'class_ids' => 'classes',
            'section_ids' => 'sections',
            'publish_date' => 'publish date',
            'expiry_date' => 'expiry date',
        ];
    }
}
