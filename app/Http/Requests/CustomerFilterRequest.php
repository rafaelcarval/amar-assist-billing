<?php

namespace App\Http\Requests;

use App\Enums\CustomerStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('document')) {
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
                'nullable',
                'string',
                'max:150',
            ],

            'document' => [
                'nullable',
                'string',
                'max:14',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    CustomerStatus::ACTIVE->value,
                    CustomerStatus::INACTIVE->value,
                ]),
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }
}