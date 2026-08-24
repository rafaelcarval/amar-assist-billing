<?php

namespace App\Services;

use App\Data\ChargeCalculation;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use InvalidArgumentException;

final class ChargeCalculator
{
    public function calculate(
        string $baseAmount,
        CarbonInterface $dueDate,
        CarbonInterface $referenceDate
    ): ChargeCalculation {
        $baseCents = $this->toCents($baseAmount);

        if ($baseCents <= 0) {
            throw new InvalidArgumentException(
                'Base amount must be greater than zero.'
            );
        }

        $due = CarbonImmutable::instance($dueDate)
            ->startOfDay();

        $reference = CarbonImmutable::instance($referenceDate)
            ->startOfDay();

        $daysLate = $reference->greaterThan($due)
            ? $due->diffInDays($reference)
            : 0;

        $lateFeeCents = $this->calculateLateFee(
            $baseCents,
            $daysLate
        );

        $totalCents = $baseCents + $lateFeeCents;

        return new ChargeCalculation(
            baseAmount: $this->fromCents($baseCents),
            lateFeeAmount: $this->fromCents($lateFeeCents),
            totalAmount: $this->fromCents($totalCents),
            daysLate: $daysLate
        );
    }

    private function calculateLateFee(
        int $baseCents,
        int $daysLate
    ): int {
        if ($daysLate === 0) {
            return 0;
        }

        /*
         * 1% ao dia.
         *
         * Exemplo:
         * 10000 centavos × 5 dias / 100
         * = 500 centavos
         * = R$ 5,00
         *
         * +50 permite arredondamento half-up
         * antes da divisão inteira por 100.
         */
        return intdiv(
            ($baseCents * $daysLate) + 50,
            100
        );
    }

    private function toCents(string $amount): int
    {
        if (! preg_match(
            '/^\d+(?:\.\d{1,2})?$/',
            $amount
        )) {
            throw new InvalidArgumentException(
                'Amount must be a valid decimal monetary value.'
            );
        }

        [$integer, $decimal] = array_pad(
            explode('.', $amount, 2),
            2,
            ''
        );

        $decimal = str_pad(
            $decimal,
            2,
            '0'
        );

        return ((int) $integer * 100)
            + (int) $decimal;
    }

    private function fromCents(int $cents): string
    {
        return sprintf(
            '%d.%02d',
            intdiv($cents, 100),
            $cents % 100
        );
    }
}