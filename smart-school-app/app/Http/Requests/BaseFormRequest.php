<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

/**
 * Base Form Request
 * 
 * Prompt 297: Standardize Validation Errors for Web and JSON
 * 
 * Provides consistent validation error handling for all form requests.
 * Extends this class for all custom form requests.
 */
abstract class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson() || $this->is('api/*')) {
            $errors = $validator->errors()->toArray();
            
            // Format errors for consistent structure
            $formattedErrors = [];
            foreach ($errors as $field => $messages) {
                $formattedErrors[$field] = [
                    'field' => $field,
                    'messages' => $messages,
                    'first' => $messages[0] ?? null,
                ];
            }

            throw new HttpResponseException(
                response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $formattedErrors,
                    'error_count' => count($formattedErrors),
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        parent::failedValidation($validator);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return array_merge($this->defaultMessages(), $this->customMessages());
    }

    /**
     * Get default validation messages.
     *
     * @return array
     */
    protected function defaultMessages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'string' => 'The :attribute must be a string.',
            'email' => 'The :attribute must be a valid email address.',
            'max' => 'The :attribute must not exceed :max characters.',
            'min' => 'The :attribute must be at least :min characters.',
            'unique' => 'The :attribute has already been taken.',
            'exists' => 'The selected :attribute is invalid.',
            'numeric' => 'The :attribute must be a number.',
            'integer' => 'The :attribute must be an integer.',
            'date' => 'The :attribute must be a valid date.',
            'date_format' => 'The :attribute does not match the format :format.',
            'before' => 'The :attribute must be a date before :date.',
            'after' => 'The :attribute must be a date after :date.',
            'in' => 'The selected :attribute is invalid.',
            'array' => 'The :attribute must be an array.',
            'boolean' => 'The :attribute must be true or false.',
            'confirmed' => 'The :attribute confirmation does not match.',
            'image' => 'The :attribute must be an image.',
            'mimes' => 'The :attribute must be a file of type: :values.',
            'file' => 'The :attribute must be a file.',
            'size' => 'The :attribute must be :size kilobytes.',
            'between' => 'The :attribute must be between :min and :max.',
            'regex' => 'The :attribute format is invalid.',
        ];
    }

    /**
     * Get custom validation messages for specific form request.
     * Override this method in child classes.
     *
     * @return array
     */
    protected function customMessages(): array
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return array_merge($this->defaultAttributes(), $this->customAttributes());
    }

    /**
     * Get default attribute names.
     *
     * @return array
     */
    protected function defaultAttributes(): array
    {
        return [
            'academic_session_id' => 'academic session',
            'class_id' => 'class',
            'section_id' => 'section',
            'subject_id' => 'subject',
            'student_id' => 'student',
            'teacher_id' => 'teacher',
            'user_id' => 'user',
            'exam_id' => 'exam',
            'fees_type_id' => 'fees type',
            'fees_group_id' => 'fees group',
        ];
    }

    /**
     * Get custom attribute names for specific form request.
     * Override this method in child classes.
     *
     * @return array
     */
    protected function customAttributes(): array
    {
        return [];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Trim string inputs
        $this->trimStrings();
        
        // Convert empty strings to null
        $this->convertEmptyStringsToNull();
    }

    /**
     * Trim all string inputs.
     *
     * @return void
     */
    protected function trimStrings(): void
    {
        $input = $this->all();
        
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
            }
        });
        
        $this->replace($input);
    }

    /**
     * Convert empty strings to null.
     *
     * @return void
     */
    protected function convertEmptyStringsToNull(): void
    {
        $input = $this->all();
        
        array_walk_recursive($input, function (&$value) {
            if ($value === '') {
                $value = null;
            }
        });
        
        $this->replace($input);
    }

    /**
     * Get validated data with defaults.
     *
     * @param array $defaults
     * @return array
     */
    public function validatedWithDefaults(array $defaults = []): array
    {
        return array_merge($defaults, $this->validated());
    }
}
