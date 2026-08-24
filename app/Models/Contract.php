<?php

namespace App\Models;

use App\Enums\ContractType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'type',
        'billing_cycle_day',
        'active',
    ];

    protected $casts = [
        'type' => ContractType::class,
        'billing_cycle_day' => 'integer',
        'active' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(Charge::class);
    }
}