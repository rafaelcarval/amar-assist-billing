<?php

namespace App\Http\Requests;

use App\Enums\CustomerStatus;
use App\Rules\CpfCnpj;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('document')) {
            $this->merge([
                'document' => preg_replace(
                    '/\D/',
                    '',
                    (string) $this->document
                ),
            ]);
        }
    }

    public function rules(): array
    {
        $customer = $this->route('customer');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'document' => [
                'sometimes',
                'required',
                'string',
                new CpfCnpj(),

                Rule::unique(
                    'customers',
                    'document'
                )->ignore($customer?->id),
            ],

            'address' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'contact' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'status' => [
                'sometimes',
                Rule::in([
                    CustomerStatus::ACTIVE->value,
                    CustomerStatus::INACTIVE->value,
                ]),
            ],
        ];
    }
}