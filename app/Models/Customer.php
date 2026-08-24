<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'document',
        'address',
        'contact',
        'status',
    ];

    protected $casts = [
        'status' => CustomerStatus::class,
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}