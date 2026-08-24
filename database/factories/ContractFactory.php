<?php

namespace Database\Factories;

use App\Enums\ContractType;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),

            'type' => fake()->randomElement([
                ContractType::PF,
                ContractType::PJ,
            ]),

            'billing_cycle_day' => fake()
                ->numberBetween(1, 31),

            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'active' => false,
        ]);
    }
}