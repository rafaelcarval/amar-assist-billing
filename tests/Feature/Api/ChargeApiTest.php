<?php

namespace Tests\Feature\Api;

use App\Enums\ChargeStatus;
use App\Models\Charge;
use App\Models\Contract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class ChargeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(
            User::factory()->create()
        );
    }

    public function test_it_generates_pix_charge(): void
    {
        $this->travelTo(
            Carbon::parse('2026-08-25')
        );

        $contract = Contract::factory()->create([
            'billing_cycle_day' => 20,
        ]);

        $response = $this->postJson(
            "/api/contracts/{$contract->id}/charges",
            [
                'base_amount' => '1000.00',
                'payment_method' => 'PIX',
                'pix_key' => 'billing@amar.test',
            ]
        );

        $response
            ->assertSuccessful()
            ->assertJsonPath(
                'data.due_date',
                '2026-08-20'
            )
            ->assertJsonPath(
                'data.late_fee_amount',
                '50.00'
            )
            ->assertJsonPath(
                'data.total_amount',
                '1050.00'
            );

        $this->travelBack();
    }

    public function test_pix_requires_pix_key(): void
    {
        $contract = Contract::factory()->create();

        $this
            ->postJson(
                "/api/contracts/{$contract->id}/charges",
                [
                    'base_amount' => '100.00',
                    'payment_method' => 'PIX',
                ]
            )
            ->assertStatus(422)
            ->assertJsonValidationErrors(
                'pix_key'
            );
    }

    public function test_it_marks_charge_as_paid(): void
    {
        $charge = Charge::factory()->create([
            'status' => ChargeStatus::OPEN,
        ]);

        $response = $this->patchJson(
            "/api/charges/{$charge->id}/pay"
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.status',
                'PAID'
            );

        $this->assertNotNull(
            $charge->fresh()->paid_at
        );
    }

    public function test_overdue_open_charges_are_listed_first(): void
    {
        $paid = Charge::factory()
            ->paid()
            ->create();

        $future = Charge::factory()
            ->create([
                'due_date' =>
                    now()->addDays(5),
            ]);

        $overdue = Charge::factory()
            ->overdue()
            ->create();

        $response = $this->getJson(
            '/api/charges'
        );

        $response->assertOk();

        $this->assertSame(
            $overdue->id,
            $response->json('data.0.id')
        );
    }
}