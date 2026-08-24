<?php

namespace Tests\Unit\Services;

use App\Services\BillingCycleService;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BillingCycleServiceTest extends TestCase
{
    private BillingCycleService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new BillingCycleService();
    }

    public function test_it_keeps_cycle_day_when_day_exists_in_month(): void
    {
        $referenceDate = CarbonImmutable::create(
            2026,
            8,
            24
        );

        $dueDate = $this->service->calculateDueDate(
            15,
            $referenceDate
        );

        $this->assertSame(
            '2026-08-15',
            $dueDate->toDateString()
        );
    }

    public function test_cycle_31_becomes_30_in_april(): void
    {
        $referenceDate = CarbonImmutable::create(
            2026,
            4,
            10
        );

        $dueDate = $this->service->calculateDueDate(
            31,
            $referenceDate
        );

        $this->assertSame(
            '2026-04-30',
            $dueDate->toDateString()
        );
    }

    public function test_cycle_31_becomes_28_in_non_leap_february(): void
    {
        $referenceDate = CarbonImmutable::create(
            2026,
            2,
            10
        );

        $dueDate = $this->service->calculateDueDate(
            31,
            $referenceDate
        );

        $this->assertSame(
            '2026-02-28',
            $dueDate->toDateString()
        );
    }

    public function test_cycle_31_becomes_29_in_leap_year(): void
    {
        $referenceDate = CarbonImmutable::create(
            2028,
            2,
            10
        );

        $dueDate = $this->service->calculateDueDate(
            31,
            $referenceDate
        );

        $this->assertSame(
            '2028-02-29',
            $dueDate->toDateString()
        );
    }

    public function test_it_rejects_cycle_day_below_one(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->service->calculateDueDate(
            0,
            CarbonImmutable::now()
        );
    }

    public function test_it_rejects_cycle_day_above_31(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->service->calculateDueDate(
            32,
            CarbonImmutable::now()
        );
    }
}