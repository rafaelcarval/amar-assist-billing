<?php

namespace App\Http\Controllers\Api;

use App\Enums\CustomerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerFilterRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customerService
    ) {
    }

    /**
     * Listar clientes
     *
     * @group Clientes
     */
    public function index(
        CustomerFilterRequest $request
    ): AnonymousResourceCollection {
        $filters = $request->validated();

        $customers = Customer::query()
            ->withCount('contracts')

            ->when(
                $filters['name'] ?? null,
                fn ($query, $name) =>
                    $query->where(
                        'name',
                        'like',
                        "%{$name}%"
                    )
            )

            ->when(
                $filters['document'] ?? null,
                fn ($query, $document) =>
                    $query->where(
                        'document',
                        'like',
                        "%{$document}%"
                    )
            )

            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) =>
                    $query->where(
                        'status',
                        $status
                    )
            )

            ->orderBy('name')

            ->paginate(
                $filters['per_page'] ?? 15
            )

            ->withQueryString();

        return CustomerResource::collection(
            $customers
        );
    }

    /**
     * Criar cliente
     *
     * @group Clientes
     */
    public function store(
        StoreCustomerRequest $request
    ): CustomerResource {
        $data = $request->validated();
    
        $data['status'] ??= CustomerStatus::ACTIVE->value;
    
        $customer = Customer::create($data);
    
        return new CustomerResource($customer);
    }

    /**
     * Exibir cliente
     *
     * @group Clientes
     */
    public function show(
        Customer $customer
    ): CustomerResource {
        $customer->loadCount('contracts');

        return new CustomerResource($customer);
    }

    /**
     * Atualizar cliente
     *
     * @group Clientes
     */
    public function update(
        UpdateCustomerRequest $request,
        Customer $customer
    ): CustomerResource {
        $data = $request->validated();

        unset($data['status']);

        $customer->update($data);

        return new CustomerResource(
            $customer->refresh()
        );
    }

    /**
     * Alterar situação do cliente
     *
     * @group Clientes
     */
    public function changeStatus(
        Customer $customer
    ): CustomerResource {
        if (
            $customer->status
            === CustomerStatus::ACTIVE
        ) {
            $customer = $this
                ->customerService
                ->deactivate($customer);
        } else {
            $customer = $this
                ->customerService
                ->activate($customer);
        }

        return new CustomerResource($customer);
    }
}