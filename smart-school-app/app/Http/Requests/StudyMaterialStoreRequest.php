<?php

namespace App\Http\Requests;

/**
 * Study Material Store Request
 * 
 * Prompt 355: Create Study Material Store Form Request
 * 
 * Validates study material creation form data.
 */
class StudyMaterialStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-study-materials');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Material Information
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'material_type' => ['required', 'in:notes,assignment,reference,other'],
            
            // Class and Section
            'class_id' => ['required', 'exists:classes,id'],
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['nullable', 'exists:sections,id'],
            
            // Subject and Teacher
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:users,id'],
            
            // Attachment (Required for study materials)
            'attachment' => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:10240'],
            
            // Status
            'status' => ['required', 'in:draft,published'],
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
            'title.required' => 'The study material title is required.',
            'title.max' => 'The study material title must not exceed 255 characters.',
            'material_type.required' => 'The material type is required.',
            'material_type.in' => 'Please select a valid material type (notes, assignment, reference, or other).',
            'class_id.required' => 'The class is required.',
            'class_id.exists' => 'The selected class is invalid.',
            'section_ids.array' => 'The sections must be an array.',
            'section_ids.*.exists' => 'One or more selected sections are invalid.',
            'subject_id.required' => 'The subject is required.',
            'subject_id.exists' => 'The selected subject is invalid.',
            'teacher_id.required' => 'The teacher is required.',
            'teacher_id.exists' => 'The selected teacher is invalid.',
            'attachment.required' => 'The study material file is required.',
            'attachment.file' => 'The attachment must be a file.',
            'attachment.mimes' => 'The attachment must be a file of type: pdf, doc, docx, ppt, pptx.',
            'attachment.max' => 'The attachment size must not exceed 10MB.',
            'status.required' => 'The status is required.',
            'status.in' => 'Please select a valid status (draft or published).',
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
            'material_type' => 'material type',
            'class_id' => 'class',
            'section_ids' => 'sections',
            'subject_id' => 'subject',
            'teacher_id' => 'teacher',
        ];
    }
}
