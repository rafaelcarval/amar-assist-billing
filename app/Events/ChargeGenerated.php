<?php

namespace App\Events;

use App\Models\Charge;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChargeGenerated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public string $connection = 'redis';

    public string $queue = 'broadcasts';

    public function __construct(
        public readonly Charge $charge
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('charges'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'charge.generated';
    }

    public function broadcastWith(): array
    {
        $charge = $this->charge
            ->loadMissing(
                'contract.customer'
            );

        return [
            'charge' => [
                'id' => $charge->id,

                'contract_id' =>
                    $charge->contract_id,

                'customer' => [
                    'id' =>
                        $charge
                            ->contract
                            ->customer
                            ->id,

                    'name' =>
                        $charge
                            ->contract
                            ->customer
                            ->name,
                ],

                'payment_method' =>
                    $charge
                        ->payment_method
                        ->value,

                'base_amount' =>
                    $charge->base_amount,

                'late_fee_amount' =>
                    $charge->late_fee_amount,

                'total_amount' =>
                    $charge->total_amount,

                'due_date' =>
                    $charge
                        ->due_date
                        ->toDateString(),

                'status' =>
                    $charge
                        ->status
                        ->value,

                'created_at' =>
                    $charge
                        ->created_at
                        ?->toIso8601String(),
            ],
        ];
    }
}