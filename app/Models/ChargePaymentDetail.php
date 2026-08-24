<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChargePaymentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'charge_id',
        'barcode',
        'pix_key',
        'card_token',
        'card_brand',
        'card_last_four',
        'card_exp_month',
        'card_exp_year',
    ];

    protected $casts = [
        'card_exp_month' => 'integer',
        'card_exp_year' => 'integer',
    ];

    public function charge(): BelongsTo
    {
        return $this->belongsTo(Charge::class);
    }
}