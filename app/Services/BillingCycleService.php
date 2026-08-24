<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use InvalidArgumentException;

final class BillingCycleService
{
    public function calculateDueDate(
        int $billingCycleDay,
        CarbonInterface $referenceDate
    ): CarbonImmutable {
        if ($billingCycleDay < 1 || $billingCycleDay > 31) {
            throw new InvalidArgumentException(
                'Billing cycle day must be between 1 and 31.'
            );
        }

        $month = CarbonImmutable::instance($referenceDate)
            ->startOfMonth();

        $day = min(
            $billingCycleDay,
            $month->daysInMonth
        );

        return $month
            ->setDay($day)
            ->startOfDay();
    }
}