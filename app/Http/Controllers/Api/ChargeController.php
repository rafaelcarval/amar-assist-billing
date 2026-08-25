<?php

namespace App\Http\Controllers\Api;

use App\Enums\ChargeStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateChargeRequest;
use App\Http\Resources\ChargeResource;
use App\Models\Charge;
use App\Models\Contract;
use App\Services\ChargeService;
use App\Services\ChargeSummaryService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChargeController extends Controller
{
    public function __construct(
        private readonly ChargeService $chargeService,
        private readonly ChargeSummaryService $chargeSummaryService
    ) {
    }

    /**
     * Listar cobranças
     *
     * Cobranças abertas e atrasadas são exibidas primeiro.
     *
     * @group Cobranças
     */
    public function index(): AnonymousResourceCollection
    {
        $charges = Charge::query()
            ->with([
                'contract.customer',
                'paymentDetail',
            ])
            ->orderByRaw(
                "
                CASE
                    WHEN status = ?
                         AND due_date < ?
                    THEN 1

                    WHEN status = ?
                    THEN 2

                    ELSE 3
                END
                ",
                [
                    ChargeStatus::OPEN->value,
                    today()->toDateString(),
                    ChargeStatus::OPEN->value,
                ]
            )
            ->orderBy('due_date')
            ->paginate(15);

        return ChargeResource::collection(
            $charges
        );
    }

    /**
     * Resumo das cobranças
     *
     * Retorna os totais de cobranças abertas, vencidas e pagas,
     * além do valor total em aberto.
     *
     * O resultado é armazenado temporariamente em cache Redis.
     *
     * @group Cobranças
     */
    public function summary(): JsonResponse
    {
        $summary = $this
            ->chargeSummaryService
            ->get();

        return response()->json([
            'data' => $summary,
        ]);
    }

    /**
     * Gerar cobrança
     *
     * @group Cobranças
     */
    public function store(
        GenerateChargeRequest $request,
        Contract $contract
    ): ChargeResource {
        $data = $request->validated();

        $referenceDate = CarbonImmutable::today();

        $charge = $this
            ->chargeService
            ->generate(
                contract: $contract,

                baseAmount:
                    $data['base_amount'],

                paymentMethod:
                    PaymentMethod::from(
                        $data['payment_method']
                    ),

                referenceDate:
                    $referenceDate,

                paymentDetails:
                    $data
            );

        return new ChargeResource($charge);
    }

    /**
     * Marcar cobrança como paga
     *
     * @group Cobranças
     */
    public function markAsPaid(
        Charge $charge
    ): ChargeResource {
        $charge = $this
            ->chargeService
            ->markAsPaid($charge);

        return new ChargeResource(
            $charge->load([
                'contract.customer',
                'paymentDetail',
            ])
        );
    }
}