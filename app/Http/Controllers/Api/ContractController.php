<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractRequest;
use App\Http\Resources\ContractResource;
use App\Models\Customer;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContractController extends Controller
{
    /**
     * Listar contratos de um cliente
     *
     * @group Contratos
     */
    public function index(
        Customer $customer
    ): AnonymousResourceCollection {
        return ContractResource::collection(
            $customer
                ->contracts()
                ->latest()
                ->get()
        );
    }

    /**
     * Criar contrato
     *
     * @group Contratos
     */
    public function store(
        StoreContractRequest $request,
        Customer $customer
    ): ContractResource {
        $contract = $customer
            ->contracts()
            ->create(
                $request->validated()
            );

        return new ContractResource($contract);
    }
}