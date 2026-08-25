<?php

namespace Database\Seeders;

use App\Enums\ChargeStatus;
use App\Enums\ContractType;
use App\Enums\CustomerStatus;
use App\Enums\PaymentMethod;
use App\Models\Charge;
use App\Models\Customer;
use App\Services\ChargeSummaryService;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Cliente pessoa física
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Cobrança PIX vencida
        |--------------------------------------------------------------------------
        |
        | R$ 1.000,00
        | 5 dias de atraso
        | 1% ao dia
        | Multa: R$ 50,00
        |
        */

        $overdueCharge = Charge::updateOrCreate(
            [
                'contract_id' => $personContract->id,
                'payment_method' => PaymentMethod::PIX,
            ],
            [
                'base_amount' => '1000.00',
                'late_fee_amount' => '50.00',
                'total_amount' => '1050.00',

                'due_date' => now()
                    ->subDays(5)
                    ->toDateString(),

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

                    'barcode' => null,

                    'card_token' => null,
                    'card_brand' => null,
                    'card_last_four' => null,
                    'card_exp_month' => null,
                    'card_exp_year' => null,
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Cliente pessoa jurídica
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Cobrança de boleto em aberto
        |--------------------------------------------------------------------------
        */

        $futureCharge = Charge::updateOrCreate(
            [
                'contract_id' => $companyContract->id,
                'payment_method' => PaymentMethod::BOLETO,
            ],
            [
                'base_amount' => '800.00',
                'late_fee_amount' => '0.00',
                'total_amount' => '800.00',

                'due_date' => now()
                    ->addDays(7)
                    ->toDateString(),

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

                    'pix_key' => null,

                    'card_token' => null,
                    'card_brand' => null,
                    'card_last_four' => null,
                    'card_exp_month' => null,
                    'card_exp_year' => null,
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Cobrança de cartão já paga
        |--------------------------------------------------------------------------
        */

        $paidCharge = Charge::updateOrCreate(
            [
                'contract_id' => $personContract->id,
                'payment_method' => PaymentMethod::CARD,
            ],
            [
                'base_amount' => '300.00',
                'late_fee_amount' => '0.00',
                'total_amount' => '300.00',

                'due_date' => now()
                    ->subDays(10)
                    ->toDateString(),

                'status' => ChargeStatus::PAID,

                'paid_at' => now()
                    ->subDays(10),
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

                    'barcode' => null,
                    'pix_key' => null,
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Cliente sem contrato
        |--------------------------------------------------------------------------
        |
        | Útil para demonstrar que um cliente sem contrato
        | pode ser ativado/desativado normalmente.
        |
        */

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

        /*
        |--------------------------------------------------------------------------
        | Cache
        |--------------------------------------------------------------------------
        |
        | Como o Seeder manipula Charge diretamente, ele não passa pelo
        | ChargeService e, portanto, precisamos invalidar explicitamente
        | o resumo cacheado.
        |
        */

        app(
            ChargeSummaryService::class
        )->forget();
    }
}