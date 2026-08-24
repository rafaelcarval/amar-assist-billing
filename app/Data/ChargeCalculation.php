<?php

namespace App\Data;

final class ChargeCalculation
{
    public function __construct(
        public readonly string $baseAmount,
        public readonly string $lateFeeAmount,
        public readonly string $totalAmount,
        public readonly int $daysLate
    ) {
    }
}