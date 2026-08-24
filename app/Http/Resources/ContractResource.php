<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,

            'customer_id' => $this->customer_id,

            'type' => $this->type->value,

            'billing_cycle_day' =>
                $this->billing_cycle_day,

            'active' => $this->active,

            'created_at' =>
                $this->created_at?->toIso8601String(),
        ];
    }
}