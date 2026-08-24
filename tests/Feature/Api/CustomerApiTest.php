<?php

namespace Tests\Feature\Api;

use App\Models\Contract;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(
            User::factory()->create()
        );
    }

    public function test_it_creates_customer(): void
    {
        $response = $this->postJson(
            '/api/customers',
            [
                'name' => 'João da Silva',
                'document' => '529.982.247-25',
                'address' => 'Rua Teste, 100',
                'contact' => 'joao@email.com',
            ]
        );

        $response
            ->assertSuccessful()
            ->assertJsonPath(
                'data.name',
                'João da Silva'
            )
            ->assertJsonPath(
                'data.status',
                'ACTIVE'
            );

        $this->assertDatabaseHas(
            'customers',
            [
                'document' => '52998224725',
                'status' => 'ACTIVE',
            ]
        );
    }

    public function test_it_rejects_invalid_document(): void
    {
        $this
            ->postJson(
                '/api/customers',
                [
                    'name' => 'Cliente',
                    'document' => '111.111.111-11',
                    'address' => 'Rua Teste',
                    'contact' => 'teste@email.com',
                ]
            )
            ->assertStatus(422)
            ->assertJsonValidationErrors(
                'document'
            );
    }

    public function test_it_filters_customers_by_name(): void
    {
        Customer::factory()->create([
            'name' => 'Rafael Carvalho',
        ]);

        Customer::factory()->create([
            'name' => 'Maria Silva',
        ]);

        $response = $this->getJson(
            '/api/customers?name=Rafael'
        );

        $response
            ->assertOk()
            ->assertJsonCount(
                1,
                'data'
            );
    }

    public function test_customer_with_contract_cannot_be_deactivated(): void
    {
        $customer = Customer::factory()->create();

        Contract::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $this
            ->patchJson(
                "/api/customers/{$customer->id}/status"
            )
            ->assertStatus(422);
    }
}