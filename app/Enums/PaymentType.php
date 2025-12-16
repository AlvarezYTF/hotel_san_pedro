<?php

namespace App\Enums;

enum PaymentType: string
{
    case CASH = 'efectivo';
    case TRANSFERY = 'tranferencia';
}
