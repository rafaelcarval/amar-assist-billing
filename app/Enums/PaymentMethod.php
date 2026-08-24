<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BOLETO = 'BOLETO';
    case CARD = 'CARD';
    case PIX = 'PIX';
}