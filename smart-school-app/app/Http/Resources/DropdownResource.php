<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Dropdown Resource
 * 
 * Prompt 296: Build API Resource Classes for JSON Consistency
 * 
 * Transforms any model into a Select2-compatible dropdown format.
 * This is a generic resource for dropdown endpoints.
 */
class DropdownResource extends JsonResource
{
    /**
     * The text field to use.
     *
     * @var string
     */
    protected string $textField = 'name';

    /**
     * Additional fields to include.
     *
     * @var array
     */
    protected array $additionalFields = [];

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     * @param string $textField
     * @param array $additionalFields
     */
    public function __construct($resource, string $textField = 'name', array $additionalFields = [])
    {
        parent::__construct($resource);
        $this->textField = $textField;
        $this->additionalFields = $additionalFields;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'text' => $this->{$this->textField} ?? $this->name ?? 'N/A',
        ];

        // Add any additional fields
        foreach ($this->additionalFields as $field) {
            if (isset($this->{$field})) {
                $data[$field] = $this->{$field};
            }
        }

        return $data;
    }

    /**
     * Create a collection with custom text field.
     *
     * @param mixed $resource
     * @param string $textField
     * @param array $additionalFields
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public static function collectionWithTextField($resource, string $textField = 'name', array $additionalFields = [])
    {
        return $resource->map(function ($item) use ($textField, $additionalFields) {
            return new static($item, $textField, $additionalFields);
        });
    }
}
