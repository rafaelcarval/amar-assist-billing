<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'document' => $this->document,

            'address' => $this->address,

            'contact' => $this->contact,

            'status' => $this->status->value,

            'contracts_count' =>
                $this->whenCounted('contracts'),

            'created_at' =>
                $this->created_at?->toIso8601String(),

            'updated_at' =>
                $this->updated_at?->toIso8601String(),
        ];
    }
}