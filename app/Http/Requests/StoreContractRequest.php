<?php

namespace App\Http\Requests;

use App\Enums\ContractType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                Rule::in([
                    ContractType::PF->value,
                    ContractType::PJ->value,
                ]),
            ],

            'billing_cycle_day' => [
                'required',
                'integer',
                'between:1,31',
            ],

            'active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}