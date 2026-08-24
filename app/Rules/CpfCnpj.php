<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CpfCnpj implements Rule
{
    public function passes($attribute, $value): bool
    {
        $document = preg_replace('/\D/', '', (string) $value);

        if (strlen($document) === 11) {
            return $this->isValidCpf($document);
        }

        if (strlen($document) === 14) {
            return $this->isValidCnpj($document);
        }

        return false;
    }

    public function message(): string
    {
        return 'O campo :attribute deve conter um CPF ou CNPJ válido.';
    }

    private function isValidCpf(string $cpf): bool
    {
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($digit = 9; $digit < 11; $digit++) {
            $sum = 0;

            for ($i = 0; $i < $digit; $i++) {
                $sum += ((int) $cpf[$i]) * (($digit + 1) - $i);
            }

            $remainder = ($sum * 10) % 11;

            if ($remainder === 10) {
                $remainder = 0;
            }

            if ((int) $cpf[$digit] !== $remainder) {
                return false;
            }
        }

        return true;
    }

    private function isValidCnpj(string $cnpj): bool
    {
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $weightsFirst = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $weightsSecond = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $firstDigit = $this->calculateCnpjDigit(
            substr($cnpj, 0, 12),
            $weightsFirst
        );

        if ((int) $cnpj[12] !== $firstDigit) {
            return false;
        }

        $secondDigit = $this->calculateCnpjDigit(
            substr($cnpj, 0, 13),
            $weightsSecond
        );

        return (int) $cnpj[13] === $secondDigit;
    }

    private function calculateCnpjDigit(
        string $base,
        array $weights
    ): int {
        $sum = 0;

        foreach ($weights as $index => $weight) {
            $sum += ((int) $base[$index]) * $weight;
        }

        $remainder = $sum % 11;

        return $remainder < 2
            ? 0
            : 11 - $remainder;
    }
}