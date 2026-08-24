<?php

namespace App\Models;

use App\Enums\ChargeStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Charge extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'payment_method',
        'base_amount',
        'late_fee_amount',
        'total_amount',
        'due_date',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'payment_method' => PaymentMethod::class,
        'status' => ChargeStatus::class,

        'base_amount' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',

        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function paymentDetail(): HasOne
    {
        return $this->hasOne(
            ChargePaymentDetail::class
        );
    }
}