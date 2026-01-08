<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeesAllotment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'fees_master_id',
        'discount_id',
        'discount_amount',
        'net_amount',
        'due_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'due_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feesMaster(): BelongsTo
    {
        return $this->belongsTo(FeesMaster::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(FeesDiscount::class, 'discount_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FeesTransaction::class, 'fees_allotment_id');
    }
}
