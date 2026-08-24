<?php

namespace Tests\Feature\Services;

use App\Enums\CustomerStatus;
use App\Exceptions\CustomerHasContractException;
use App\Models\Contract;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    private CustomerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CustomerService::class);
    }

    public function test_customer_without_contract_can_be_deactivated(): void
    {
        $customer = Customer::factory()->create([
            'status' => CustomerStatus::ACTIVE,
        ]);

        $customer = $this->service->deactivate(
            $customer
        );

        $this->assertSame(
            CustomerStatus::INACTIVE,
            $customer->status
        );

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'status' => CustomerStatus::INACTIVE->value,
        ]);
    }

    public function test_customer_with_contract_cannot_be_deactivated(): void
    {
        $customer = Customer::factory()->create();

        Contract::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $this->expectException(
            CustomerHasContractException::class
        );

        $this->service->deactivate($customer);
    }

    public function test_even_inactive_contract_prevents_customer_deactivation(): void
    {
        $customer = Customer::factory()->create();

        Contract::factory()
            ->inactive()
            ->create([
                'customer_id' => $customer->id,
            ]);

        $this->expectException(
            CustomerHasContractException::class
        );

        $this->service->deactivate($customer);
    }

    public function test_inactive_customer_can_be_activated(): void
    {
        $customer = Customer::factory()
            ->inactive()
            ->create();

        $customer = $this->service->activate(
            $customer
        );

        $this->assertSame(
            CustomerStatus::ACTIVE,
            $customer->status
        );
    }
}