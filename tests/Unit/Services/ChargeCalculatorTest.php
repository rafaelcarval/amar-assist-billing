<?php

namespace Tests\Unit\Services;

use App\Services\ChargeCalculator;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ChargeCalculatorTest extends TestCase
{
    private ChargeCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new ChargeCalculator();
    }

    public function test_it_does_not_apply_fee_before_due_date(): void
    {
        $calculation = $this->calculator->calculate(
            '100.00',
            CarbonImmutable::create(2026, 8, 25),
            CarbonImmutable::create(2026, 8, 24)
        );

        $this->assertSame(0, $calculation->daysLate);
        $this->assertSame('0.00', $calculation->lateFeeAmount);
        $this->assertSame('100.00', $calculation->totalAmount);
    }

    public function test_it_does_not_apply_fee_on_due_date(): void
    {
        $calculation = $this->calculator->calculate(
            '100.00',
            CarbonImmutable::create(2026, 8, 24),
            CarbonImmutable::create(2026, 8, 24)
        );

        $this->assertSame(0, $calculation->daysLate);
        $this->assertSame('0.00', $calculation->lateFeeAmount);
        $this->assertSame('100.00', $calculation->totalAmount);
    }

    public function test_it_applies_one_percent_for_one_day_late(): void
    {
        $calculation = $this->calculator->calculate(
            '100.00',
            CarbonImmutable::create(2026, 8, 23),
            CarbonImmutable::create(2026, 8, 24)
        );

        $this->assertSame(1, $calculation->daysLate);
        $this->assertSame('1.00', $calculation->lateFeeAmount);
        $this->assertSame('101.00', $calculation->totalAmount);
    }

    public function test_it_applies_one_percent_per_day(): void
    {
        $calculation = $this->calculator->calculate(
            '1000.00',
            CarbonImmutable::create(2026, 8, 19),
            CarbonImmutable::create(2026, 8, 24)
        );

        $this->assertSame(5, $calculation->daysLate);
        $this->assertSame('50.00', $calculation->lateFeeAmount);
        $this->assertSame('1050.00', $calculation->totalAmount);
    }

    public function test_it_rounds_fee_to_nearest_cent(): void
    {
        $calculation = $this->calculator->calculate(
            '10.99',
            CarbonImmutable::create(2026, 8, 23),
            CarbonImmutable::create(2026, 8, 24)
        );

        $this->assertSame('0.11', $calculation->lateFeeAmount);
        $this->assertSame('11.10', $calculation->totalAmount);
    }

    public function test_it_rejects_zero_amount(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->calculator->calculate(
            '0.00',
            CarbonImmutable::create(2026, 8, 20),
            CarbonImmutable::create(2026, 8, 24)
        );
    }

    public function test_it_rejects_invalid_money_format(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->calculator->calculate(
            '100,00',
            CarbonImmutable::create(2026, 8, 20),
            CarbonImmutable::create(2026, 8, 24)
        );
    }
}