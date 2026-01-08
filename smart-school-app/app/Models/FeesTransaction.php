<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FeesTransaction Model
 * 
 * Prompt 84: Create FeesTransaction Model with relationships to Student,
 * FeesAllotment, User (received_by).
 * 
 * @property int $id
 * @property int $student_id
 * @property int $fees_allotment_id
 * @property string $transaction_id
 * @property float $amount
 * @property string $payment_method
 * @property string $payment_status
 * @property \Carbon\Carbon $payment_date
 * @property \Carbon\Carbon $transaction_date
 * @property string|null $reference_number
 * @property string|null $bank_name
 * @property string|null $cheque_number
 * @property string|null $remarks
 * @property int|null $received_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class FeesTransaction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fees_transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'fees_allotment_id',
        'transaction_id',
        'amount',
        'payment_method',
        'payment_status',
        'payment_date',
        'transaction_date',
        'reference_number',
        'bank_name',
        'cheque_number',
        'remarks',
        'received_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'transaction_date' => 'datetime',
        ];
    }

    /**
     * Get the student for this transaction.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the fees allotment for this transaction.
     */
    public function feesAllotment(): BelongsTo
    {
        return $this->belongsTo(FeesAllotment::class, 'fees_allotment_id');
    }

    /**
     * Get the user who received this payment.
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Scope a query to filter by student.
     */
    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query to filter by allotment.
     */
    public function scopeForAllotment($query, int $allotmentId)
    {
        return $query->where('fees_allotment_id', $allotmentId);
    }

    /**
     * Scope a query to filter by payment method.
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope a query to filter by payment status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope a query to filter completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    /**
     * Scope a query to filter pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope a query to filter failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('payment_status', 'failed');
    }

    /**
     * Scope a query to filter refunded transactions.
     */
    public function scopeRefunded($query)
    {
        return $query->where('payment_status', 'refunded');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter today's transactions.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', now());
    }

    /**
     * Check if this transaction is completed.
     */
    public function isCompleted(): bool
    {
        return $this->payment_status === 'completed';
    }

    /**
     * Check if this transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    /**
     * Check if this transaction is failed.
     */
    public function isFailed(): bool
    {
        return $this->payment_status === 'failed';
    }

    /**
     * Check if this transaction is refunded.
     */
    public function isRefunded(): bool
    {
        return $this->payment_status === 'refunded';
    }

    /**
     * Check if this is a cash payment.
     */
    public function isCash(): bool
    {
        return $this->payment_method === 'cash';
    }

    /**
     * Check if this is a cheque payment.
     */
    public function isCheque(): bool
    {
        return $this->payment_method === 'cheque';
    }

    /**
     * Check if this is an online payment.
     */
    public function isOnline(): bool
    {
        return $this->payment_method === 'online';
    }

    /**
     * Get the payment method display name.
     */
    public function getPaymentMethodDisplayAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => 'Cash',
            'cheque' => 'Cheque',
            'dd' => 'Demand Draft',
            'online' => 'Online',
            default => ucfirst($this->payment_method),
        };
    }

    /**
     * Get the payment status display name.
     */
    public function getPaymentStatusDisplayAttribute(): string
    {
        return ucfirst($this->payment_status);
    }
}
