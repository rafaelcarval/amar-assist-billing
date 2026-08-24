<?php

namespace App\Enums;

enum CustomerStatus: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
}