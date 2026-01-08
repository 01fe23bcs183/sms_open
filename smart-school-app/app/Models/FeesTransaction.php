<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeesTransaction extends Model
{
    use HasFactory;

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

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'transaction_date' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feesAllotment(): BelongsTo
    {
        return $this->belongsTo(FeesAllotment::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
