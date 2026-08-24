<?php

namespace App\Services;

use App\Enums\CustomerStatus;
use App\Exceptions\CustomerHasContractException;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

final class CustomerService
{
    public function deactivate(
        Customer $customer
    ): Customer {
        return DB::transaction(function () use ($customer) {
            $lockedCustomer = Customer::query()
                ->lockForUpdate()
                ->findOrFail($customer->getKey());

            if ($lockedCustomer->contracts()->exists()) {
                throw new CustomerHasContractException();
            }

            $lockedCustomer->update([
                'status' => CustomerStatus::INACTIVE,
            ]);

            return $lockedCustomer->refresh();
        });
    }

    public function activate(
        Customer $customer
    ): Customer {
        $customer->update([
            'status' => CustomerStatus::ACTIVE,
        ]);

        return $customer->refresh();
    }
}