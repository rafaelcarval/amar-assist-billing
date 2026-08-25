<?php

namespace App\Services;

use App\Enums\ChargeStatus;
use App\Enums\PaymentMethod;
use App\Events\ChargeGenerated;
use App\Models\Charge;
use App\Models\Contract;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

final class ChargeService
{
    public function __construct(
        private readonly BillingCycleService $billingCycleService,
        private readonly ChargeCalculator $chargeCalculator,
        private readonly ChargeSummaryService $chargeSummaryService
    ) {
    }

    public function generate(
        Contract $contract,
        string $baseAmount,
        PaymentMethod $paymentMethod,
        CarbonInterface $referenceDate,
        array $paymentDetails = []
    ): Charge {
        $charge = DB::transaction(
            function () use (
                $contract,
                $baseAmount,
                $paymentMethod,
                $referenceDate,
                $paymentDetails
            ) {
                $dueDate = $this
                    ->billingCycleService
                    ->calculateDueDate(
                        $contract->billing_cycle_day,
                        $referenceDate
                    );

                $calculation = $this
                    ->chargeCalculator
                    ->calculate(
                        $baseAmount,
                        $dueDate,
                        $referenceDate
                    );

                $charge = $contract
                    ->charges()
                    ->create([
                        'payment_method' =>
                            $paymentMethod,

                        'base_amount' =>
                            $calculation->baseAmount,

                        'late_fee_amount' =>
                            $calculation->lateFeeAmount,

                        'total_amount' =>
                            $calculation->totalAmount,

                        'due_date' =>
                            $dueDate->toDateString(),

                        'status' =>
                            ChargeStatus::OPEN,

                        'paid_at' =>
                            null,
                    ]);

                $charge
                    ->paymentDetail()
                    ->create(
                        $this->sanitizePaymentDetails(
                            $paymentMethod,
                            $paymentDetails
                        )
                    );

                return $charge->load(
                    'paymentDetail',
                    'contract.customer'
                );
            }
        );

        /*
         * A criação da cobrança altera os dados
         * utilizados pelo resumo.
         */
        $this
            ->chargeSummaryService
            ->forget();

        /*
         * O evento é disparado somente depois
         * do commit da transação.
         */
        event(
            new ChargeGenerated($charge)
        );

        return $charge;
    }

    public function markAsPaid(
        Charge $charge
    ): Charge {
        if (
            $charge->status === ChargeStatus::PAID
        ) {
            return $charge;
        }

        $charge->update([
            'status' => ChargeStatus::PAID,
            'paid_at' => now(),
        ]);

        /*
         * O resumo mudou:
         *
         * - quantidade de abertas
         * - quantidade de pagas
         * - quantidade de vencidas, possivelmente
         * - valor total em aberto
         */
        $this
            ->chargeSummaryService
            ->forget();

        return $charge->refresh();
    }

    private function sanitizePaymentDetails(
        PaymentMethod $paymentMethod,
        array $details
    ): array {
        return match ($paymentMethod) {
            PaymentMethod::BOLETO => [
                'barcode' =>
                    $details['barcode'] ?? null,
            ],

            PaymentMethod::PIX => [
                'pix_key' =>
                    $details['pix_key'] ?? null,
            ],

            PaymentMethod::CARD => [
                'card_token' =>
                    $details['card_token'] ?? null,

                'card_brand' =>
                    $details['card_brand'] ?? null,

                'card_last_four' =>
                    $details['card_last_four'] ?? null,

                'card_exp_month' =>
                    $details['card_exp_month'] ?? null,

                'card_exp_year' =>
                    $details['card_exp_year'] ?? null,
            ],
        };
    }
}