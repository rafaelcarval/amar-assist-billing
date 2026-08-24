<?php

namespace Tests\Feature\Services;

use App\Enums\ChargeStatus;
use App\Enums\PaymentMethod;
use App\Models\Contract;
use App\Services\ChargeService;
use App\Events\ChargeGenerated;
use Illuminate\Support\Facades\Event;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChargeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_charge_without_fee_when_not_late(): void
    {
        $contract = Contract::factory()->create([
            'billing_cycle_day' => 25,
        ]);

        $service = app(ChargeService::class);

        $charge = $service->generate(
            contract: $contract,
            baseAmount: '1000.00',
            paymentMethod: PaymentMethod::PIX,
            referenceDate: CarbonImmutable::create(
                2026,
                8,
                20
            )
        );

        $this->assertSame(
            '2026-08-25',
            $charge->due_date->toDateString()
        );

        $this->assertSame(
            '1000.00',
            $charge->base_amount
        );

        $this->assertSame(
            '0.00',
            $charge->late_fee_amount
        );

        $this->assertSame(
            '1000.00',
            $charge->total_amount
        );

        $this->assertSame(
            ChargeStatus::OPEN,
            $charge->status
        );
    }

    public function test_it_applies_fee_when_cycle_is_already_overdue(): void
    {
        $contract = Contract::factory()->create([
            'billing_cycle_day' => 20,
        ]);

        $service = app(ChargeService::class);

        $charge = $service->generate(
            contract: $contract,
            baseAmount: '1000.00',
            paymentMethod: PaymentMethod::BOLETO,
            referenceDate: CarbonImmutable::create(
                2026,
                8,
                25
            )
        );

        $this->assertSame(
            '2026-08-20',
            $charge->due_date->toDateString()
        );

        $this->assertSame(
            '50.00',
            $charge->late_fee_amount
        );

        $this->assertSame(
            '1050.00',
            $charge->total_amount
        );
    }

    public function test_it_respects_last_day_of_february(): void
    {
        $contract = Contract::factory()->create([
            'billing_cycle_day' => 31,
        ]);

        $service = app(ChargeService::class);

        $charge = $service->generate(
            contract: $contract,
            baseAmount: '500.00',
            paymentMethod: PaymentMethod::PIX,
            referenceDate: CarbonImmutable::create(
                2026,
                2,
                20
            )
        );

        $this->assertSame(
            '2026-02-28',
            $charge->due_date->toDateString()
        );
    }

    public function test_it_dispatches_charge_generated_event(): void
    {
        Event::fake([
            ChargeGenerated::class,
        ]);

        $contract = Contract::factory()->create([
            'billing_cycle_day' => 20,
        ]);

        $service = app(ChargeService::class);

        $charge = $service->generate(
            contract: $contract,

            baseAmount: '100.00',

            paymentMethod: PaymentMethod::PIX,

            referenceDate:
                CarbonImmutable::create(
                    2026,
                    8,
                    20
                ),

            paymentDetails: [
                'pix_key' => 'teste@amar.test',
            ]
        );

        Event::assertDispatched(
            ChargeGenerated::class,
            function (
                ChargeGenerated $event
            ) use ($charge) {
                return $event->charge->id
                    === $charge->id;
            }
        );
    }
}