<?php

namespace Database\Factories;

use App\Enums\ChargeStatus;
use App\Enums\PaymentMethod;
use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChargeFactory extends Factory
{
    public function definition(): array
    {
        $amount = fake()->randomFloat(
            2,
            100,
            5000
        );

        return [
            'contract_id' => Contract::factory(),

            'payment_method' => fake()->randomElement([
                PaymentMethod::BOLETO,
                PaymentMethod::CARD,
                PaymentMethod::PIX,
            ]),

            'base_amount' => $amount,

            'late_fee_amount' => 0,

            'total_amount' => $amount,

            'due_date' => now()
                ->addDays(
                    fake()->numberBetween(1, 30)
                )
                ->toDateString(),

            'status' => ChargeStatus::OPEN,

            'paid_at' => null,
        ];
    }

    public function overdue(): static
    {
        return $this->state(function () {
            return [
                'due_date' => now()
                    ->subDays(5)
                    ->toDateString(),

                'status' => ChargeStatus::OPEN,
            ];
        });
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => ChargeStatus::PAID,
            'paid_at' => now(),
        ]);
    }
}