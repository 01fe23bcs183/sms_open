<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * FeesAllotment Model
 * 
 * Prompt 83: Create FeesAllotment Model with relationships to Student, FeesMaster,
 * FeesDiscount, FeesTransaction.
 * 
 * @property int $id
 * @property int $student_id
 * @property int $fees_master_id
 * @property int|null $discount_id
 * @property float $discount_amount
 * @property float $net_amount
 * @property \Carbon\Carbon|null $due_date
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class FeesAllotment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fees_allotments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'fees_master_id',
        'discount_id',
        'discount_amount',
        'net_amount',
        'due_date',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'due_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the student for this allotment.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the fees master for this allotment.
     */
    public function feesMaster(): BelongsTo
    {
        return $this->belongsTo(FeesMaster::class, 'fees_master_id');
    }

    /**
     * Get the discount for this allotment.
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(FeesDiscount::class, 'discount_id');
    }

    /**
     * Get the transactions for this allotment.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(FeesTransaction::class, 'fees_allotment_id');
    }

    /**
     * Scope a query to only include active allotments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by student.
     */
    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query to filter by fees master.
     */
    public function scopeForFeesMaster($query, int $feesMasterId)
    {
        return $query->where('fees_master_id', $feesMasterId);
    }

    /**
     * Scope a query to filter overdue allotments.
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
                     ->where('due_date', '<', now());
    }

    /**
     * Scope a query to filter upcoming due allotments.
     */
    public function scopeUpcomingDue($query, int $days = 7)
    {
        return $query->whereNotNull('due_date')
                     ->where('due_date', '>=', now())
                     ->where('due_date', '<=', now()->addDays($days));
    }

    /**
     * Check if this allotment is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if this allotment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }

    /**
     * Get the total paid amount for this allotment.
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->transactions()
                    ->where('payment_status', 'completed')
                    ->sum('amount');
    }

    /**
     * Get the balance amount for this allotment.
     */
    public function getBalanceAttribute(): float
    {
        return $this->net_amount - $this->total_paid;
    }

    /**
     * Check if this allotment is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->balance <= 0;
    }

    /**
     * Check if this allotment is partially paid.
     */
    public function isPartiallyPaid(): bool
    {
        return $this->total_paid > 0 && $this->balance > 0;
    }

    /**
     * Check if this allotment is unpaid.
     */
    public function isUnpaid(): bool
    {
        return $this->total_paid == 0;
    }

    /**
     * Get the payment status for this allotment.
     */
    public function getPaymentStatusAttribute(): string
    {
        if ($this->isFullyPaid()) {
            return 'paid';
        }
        if ($this->isPartiallyPaid()) {
            return 'partial';
        }
        return 'unpaid';
    }

    /**
     * Get the days until due date.
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date) {
            return null;
        }
        return now()->diffInDays($this->due_date, false);
    }
}
