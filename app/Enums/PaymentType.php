<?php

namespace App\Enums;

enum PaymentType: string
{
    case Room          = 'room';
    case Deposit       = 'deposit';
    case DepositReturn = 'deposit_return';
}
