<?php

namespace App\Http\Requests;

use App\Enums\CustomerStatus;
use App\Rules\CpfCnpj;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
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
        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'document' => [
                'required',
                'string',
                new CpfCnpj(),
                'unique:customers,document',
            ],

            'address' => [
                'required',
                'string',
                'max:255',
            ],

            'contact' => [
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