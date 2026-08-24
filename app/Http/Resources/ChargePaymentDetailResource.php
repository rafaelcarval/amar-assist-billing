<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChargePaymentDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'barcode' => $this->barcode,

            'pix_key' => $this->pix_key,

            'card_brand' => $this->card_brand,

            'card_last_four' =>
                $this->card_last_four,

            'card_exp_month' =>
                $this->card_exp_month,

            'card_exp_year' =>
                $this->card_exp_year,
        ];
    }
}