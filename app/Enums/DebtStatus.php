<?php

namespace App\Enums;

enum DebtStatus: string
{
    case Active = 'active';
    case Settled = 'settled';
    case Overdue = 'overdue';
}
