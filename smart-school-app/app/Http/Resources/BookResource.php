<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Book Resource
 * 
 * Prompt 296: Build API Resource Classes for JSON Consistency
 * 
 * Transforms Book model data into a consistent JSON format.
 */
class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'isbn' => $this->isbn,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'edition' => $this->edition,
            'publication_year' => $this->publication_year,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'available_quantity' => $this->available_quantity,
            'rack_number' => $this->rack_number,
            'description' => $this->description,
            'cover_image' => $this->cover_image ? asset('storage/' . $this->cover_image) : null,
            'is_available' => $this->available_quantity > 0,
            'is_active' => $this->is_active,
            
            // Relationships (loaded conditionally)
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'issues' => $this->whenLoaded('issues', function () {
                return $this->issues->map(function ($issue) {
                    return [
                        'id' => $issue->id,
                        'issue_date' => $issue->issue_date?->format('Y-m-d'),
                        'due_date' => $issue->due_date?->format('Y-m-d'),
                        'return_date' => $issue->return_date?->format('Y-m-d'),
                        'status' => $issue->status,
                    ];
                });
            }),
            'issues_count' => $this->whenCounted('issues'),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
