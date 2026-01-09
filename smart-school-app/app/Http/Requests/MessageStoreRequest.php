<?php

namespace App\Http\Requests;

/**
 * Message Store Request
 * 
 * Prompt 353: Create Message Store Form Request
 * 
 * Validates message creation form data.
 */
class MessageStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-messages');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Message Information
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            
            // Recipient Information
            'recipient_type' => ['required', 'in:individual,group,all'],
            'recipient_ids' => ['nullable', 'array'],
            'recipient_ids.*' => ['nullable', 'exists:users,id'],
            
            // Attachment
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
            
            // Notification Options
            'send_email' => ['nullable', 'boolean'],
            'send_sms' => ['nullable', 'boolean'],
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
            'subject.required' => 'The message subject is required.',
            'subject.max' => 'The message subject must not exceed 255 characters.',
            'message.required' => 'The message content is required.',
            'recipient_type.required' => 'The recipient type is required.',
            'recipient_type.in' => 'Please select a valid recipient type (individual, group, or all).',
            'recipient_ids.array' => 'The recipients must be an array.',
            'recipient_ids.*.exists' => 'One or more selected recipients are invalid.',
            'attachment.file' => 'The attachment must be a file.',
            'attachment.mimes' => 'The attachment must be a file of type: pdf, doc, docx, jpg, jpeg, png.',
            'attachment.max' => 'The attachment size must not exceed 5MB.',
            'send_email.boolean' => 'The send email option must be true or false.',
            'send_sms.boolean' => 'The send SMS option must be true or false.',
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
            'recipient_type' => 'recipient type',
            'recipient_ids' => 'recipients',
            'send_email' => 'send email',
            'send_sms' => 'send SMS',
        ];
    }
}
