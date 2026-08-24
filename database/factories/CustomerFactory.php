<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),

            'document' => fake()
                ->unique()
                ->numerify('###########'),

            'address' => fake()->address(),

            'contact' => fake()->email(),

            'status' => CustomerStatus::ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => CustomerStatus::INACTIVE,
        ]);
    }
}