<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Fees Transaction Collection Resource
 * 
 * Prompt 296: Build API Resource Classes for JSON Consistency
 * 
 * Wraps a collection of fees transactions with pagination metadata.
 */
class FeesTransactionCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = FeesTransactionResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        // Calculate summary statistics
        $totalAmount = $this->collection->sum('total_amount_raw');
        $totalDiscount = $this->collection->sum('discount_amount_raw');
        $totalFine = $this->collection->sum('fine_amount_raw');

        return [
            'status' => 'success',
            'message' => 'Transactions retrieved successfully',
            'summary' => [
                'total_transactions' => $this->collection->count(),
                'total_amount' => '₹' . number_format($totalAmount, 2),
                'total_discount' => '₹' . number_format($totalDiscount, 2),
                'total_fine' => '₹' . number_format($totalFine, 2),
            ],
        ];
    }
}
