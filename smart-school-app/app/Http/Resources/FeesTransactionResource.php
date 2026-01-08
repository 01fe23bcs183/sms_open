<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Fees Transaction Resource
 * 
 * Prompt 296: Build API Resource Classes for JSON Consistency
 * 
 * Transforms FeesTransaction model data into a consistent JSON format.
 */
class FeesTransactionResource extends JsonResource
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
            'receipt_number' => $this->receipt_number,
            'transaction_date' => $this->transaction_date?->format('Y-m-d'),
            'amount' => $this->formatCurrency($this->amount),
            'amount_raw' => $this->amount,
            'discount_amount' => $this->formatCurrency($this->discount_amount),
            'discount_amount_raw' => $this->discount_amount,
            'fine_amount' => $this->formatCurrency($this->fine_amount),
            'fine_amount_raw' => $this->fine_amount,
            'total_amount' => $this->formatCurrency($this->total_amount),
            'total_amount_raw' => $this->total_amount,
            'payment_method' => $this->payment_method,
            'payment_method_label' => $this->getPaymentMethodLabel(),
            'payment_status' => $this->payment_status,
            'payment_status_label' => $this->getPaymentStatusLabel(),
            'transaction_id' => $this->transaction_id,
            'cheque_number' => $this->cheque_number,
            'cheque_date' => $this->cheque_date?->format('Y-m-d'),
            'bank_name' => $this->bank_name,
            'remarks' => $this->remarks,
            
            // Relationships (loaded conditionally)
            'student' => $this->whenLoaded('student', function () {
                return [
                    'id' => $this->student->id,
                    'admission_number' => $this->student->admission_number,
                    'name' => $this->student->user?->name ?? 'N/A',
                    'class' => $this->student->schoolClass?->name,
                    'section' => $this->student->section?->name,
                ];
            }),
            'fees_allotment' => $this->whenLoaded('feesAllotment', function () {
                return [
                    'id' => $this->feesAllotment->id,
                    'fees_master' => $this->feesAllotment->feesMaster ? [
                        'id' => $this->feesAllotment->feesMaster->id,
                        'fees_type' => $this->feesAllotment->feesMaster->feesType?->name,
                        'fees_group' => $this->feesAllotment->feesMaster->feesGroup?->name,
                        'amount' => $this->feesAllotment->feesMaster->amount,
                        'due_date' => $this->feesAllotment->feesMaster->due_date?->format('Y-m-d'),
                    ] : null,
                ];
            }),
            'collected_by' => $this->whenLoaded('collectedBy', function () {
                return [
                    'id' => $this->collectedBy->id,
                    'name' => $this->collectedBy->name,
                ];
            }),
            'academic_session' => $this->whenLoaded('academicSession', function () {
                return [
                    'id' => $this->academicSession->id,
                    'name' => $this->academicSession->name,
                ];
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Format currency value.
     *
     * @param float|null $amount
     * @return string
     */
    protected function formatCurrency(?float $amount): string
    {
        if ($amount === null) {
            return '₹0.00';
        }
        return '₹' . number_format($amount, 2);
    }

    /**
     * Get payment method label.
     *
     * @return string
     */
    protected function getPaymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Cash',
            'cheque' => 'Cheque',
            'dd' => 'Demand Draft',
            'online' => 'Online Payment',
            'upi' => 'UPI',
            'card' => 'Card Payment',
            default => ucfirst($this->payment_method ?? 'Unknown'),
        };
    }

    /**
     * Get payment status label.
     *
     * @return string
     */
    protected function getPaymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->payment_status ?? 'Unknown'),
        };
    }
}
