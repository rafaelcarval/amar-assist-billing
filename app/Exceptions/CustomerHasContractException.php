<?php

namespace App\Exceptions;

use DomainException;

class CustomerHasContractException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            'Cliente com contrato não pode ser desativado.'
        );
    }
}