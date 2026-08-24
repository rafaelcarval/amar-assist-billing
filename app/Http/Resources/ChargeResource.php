<?php

namespace App\Http\Resources;

use App\Enums\ChargeStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class ChargeResource extends JsonResource
{
    public function toArray($request): array
    {
        $isOverdue =
            $this->status === ChargeStatus::OPEN
            && $this->due_date->isBefore(today());

        return [
            'id' => $this->id,

            'contract_id' => $this->contract_id,

            'customer' => [
                'id' =>
                    $this->contract?->customer?->id,

                'name' =>
                    $this->contract?->customer?->name,
            ],

            'payment_method' =>
                $this->payment_method->value,

            'base_amount' =>
                $this->base_amount,

            'late_fee_amount' =>
                $this->late_fee_amount,

            'total_amount' =>
                $this->total_amount,

            'due_date' =>
                $this->due_date->toDateString(),

            'status' =>
                $this->status->value,

            'is_overdue' =>
                $isOverdue,

            'paid_at' =>
                $this->paid_at?->toIso8601String(),

            'payment_details' =>
                new ChargePaymentDetailResource(
                    $this->whenLoaded(
                        'paymentDetail'
                    )
                ),
        ];
    }
}