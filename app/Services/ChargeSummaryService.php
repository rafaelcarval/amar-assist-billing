<?php

namespace App\Services;

use App\Enums\ChargeStatus;
use App\Models\Charge;
use Illuminate\Support\Facades\Cache;

final class ChargeSummaryService
{
    private const CACHE_KEY = 'charges:summary';

    private const CACHE_TTL_SECONDS = 60;

    public function get(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            function (): array {
                return $this->calculate();
            }
        );
    }

    public function forget(): void
    {
        Cache::forget(
            self::CACHE_KEY
        );
    }

    private function calculate(): array
    {
        $today = today()->toDateString();

        $open = Charge::query()
            ->where(
                'status',
                ChargeStatus::OPEN->value
            )
            ->count();

        $overdue = Charge::query()
            ->where(
                'status',
                ChargeStatus::OPEN->value
            )
            ->whereDate(
                'due_date',
                '<',
                $today
            )
            ->count();

        $paid = Charge::query()
            ->where(
                'status',
                ChargeStatus::PAID->value
            )
            ->count();

        $openAmount = Charge::query()
            ->where(
                'status',
                ChargeStatus::OPEN->value
            )
            ->sum('total_amount');

        return [
            'open' => $open,
            'overdue' => $overdue,
            'paid' => $paid,

            'open_amount' => number_format(
                (float) $openAmount,
                2,
                '.',
                ''
            ),
        ];
    }
}