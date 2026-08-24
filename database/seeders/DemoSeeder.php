<?php

namespace Database\Seeders;

use App\Enums\ChargeStatus;
use App\Enums\ContractType;
use App\Enums\CustomerStatus;
use App\Enums\PaymentMethod;
use App\Models\Charge;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $person = Customer::updateOrCreate(
            [
                'document' => '52998224725',
            ],
            [
                'name' => 'João da Silva',
                'address' => 'Av. Paulista, 1000 - São Paulo/SP',
                'contact' => 'joao@amar.test',
                'status' => CustomerStatus::ACTIVE,
            ]
        );

        $personContract = $person
            ->contracts()
            ->firstOrCreate(
                [
                    'type' => ContractType::PF,
                    'billing_cycle_day' => 20,
                ],
                [
                    'active' => true,
                ]
            );

        $overdueCharge = Charge::firstOrCreate(
            [
                'contract_id' => $personContract->id,
                'payment_method' => PaymentMethod::PIX,
                'due_date' => now()
                    ->subDays(5)
                    ->toDateString(),
            ],
            [
                'base_amount' => '1000.00',
                'late_fee_amount' => '50.00',
                'total_amount' => '1050.00',
                'status' => ChargeStatus::OPEN,
                'paid_at' => null,
            ]
        );

        $overdueCharge
            ->paymentDetail()
            ->updateOrCreate(
                [],
                [
                    'pix_key' => 'financeiro@amar.test',
                ]
            );

        $company = Customer::updateOrCreate(
            [
                'document' => '11222333000181',
            ],
            [
                'name' => 'Amar Tecnologia Ltda.',
                'address' => 'Rua Faria Lima, 500 - São Paulo/SP',
                'contact' => 'financeiro@empresa.test',
                'status' => CustomerStatus::ACTIVE,
            ]
        );

        $companyContract = $company
            ->contracts()
            ->firstOrCreate(
                [
                    'type' => ContractType::PJ,
                    'billing_cycle_day' => 28,
                ],
                [
                    'active' => true,
                ]
            );

        $futureCharge = Charge::firstOrCreate(
            [
                'contract_id' => $companyContract->id,
                'payment_method' => PaymentMethod::BOLETO,
                'due_date' => now()
                    ->addDays(7)
                    ->toDateString(),
            ],
            [
                'base_amount' => '800.00',
                'late_fee_amount' => '0.00',
                'total_amount' => '800.00',
                'status' => ChargeStatus::OPEN,
                'paid_at' => null,
            ]
        );

        $futureCharge
            ->paymentDetail()
            ->updateOrCreate(
                [],
                [
                    'barcode' =>
                        '34191790010104351004791020150008291070000080000',
                ]
            );

        $paidCharge = Charge::firstOrCreate(
            [
                'contract_id' => $personContract->id,
                'payment_method' => PaymentMethod::CARD,
                'due_date' => now()
                    ->subDays(10)
                    ->toDateString(),
            ],
            [
                'base_amount' => '300.00',
                'late_fee_amount' => '0.00',
                'total_amount' => '300.00',
                'status' => ChargeStatus::PAID,
                'paid_at' => now()->subDays(10),
            ]
        );

        $paidCharge
            ->paymentDetail()
            ->updateOrCreate(
                [],
                [
                    'card_token' => 'tok_demo_card',
                    'card_brand' => 'Visa',
                    'card_last_four' => '4242',
                    'card_exp_month' => 12,
                    'card_exp_year' => now()->year + 2,
                ]
            );

        Customer::updateOrCreate(
            [
                'document' => '33000167000101',
            ],
            [
                'name' => 'Cliente Sem Contrato Ltda.',
                'address' => 'Rua Augusta, 200 - São Paulo/SP',
                'contact' => 'contato@semcontrato.test',
                'status' => CustomerStatus::INACTIVE,
            ]
        );
    }
}