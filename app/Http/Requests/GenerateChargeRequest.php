<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'base_amount' => [
                'required',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],

            'payment_method' => [
                'required',
                Rule::in([
                    PaymentMethod::BOLETO->value,
                    PaymentMethod::CARD->value,
                    PaymentMethod::PIX->value,
                ]),
            ],

            'reference_date' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'barcode' => [
                'nullable',
                'string',
                'max:100',
                'required_if:payment_method,BOLETO',
            ],

            'pix_key' => [
                'nullable',
                'string',
                'max:255',
                'required_if:payment_method,PIX',
            ],

            'card_token' => [
                'nullable',
                'string',
                'max:255',
                'required_if:payment_method,CARD',
            ],

            'card_brand' => [
                'nullable',
                'string',
                'max:30',
                'required_if:payment_method,CARD',
            ],

            'card_last_four' => [
                'nullable',
                'digits:4',
                'required_if:payment_method,CARD',
            ],

            'card_exp_month' => [
                'nullable',
                'integer',
                'between:1,12',
                'required_if:payment_method,CARD',
            ],

            'card_exp_year' => [
                'nullable',
                'integer',
                'min:' . now()->year,
                'required_if:payment_method,CARD',
            ],
        ];
    }
}