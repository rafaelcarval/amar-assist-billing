<?php

namespace App\Enums;

enum ChargeStatus: string
{
    case OPEN = 'OPEN';
    case PAID = 'PAID';
}