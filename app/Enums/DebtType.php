<?php

namespace App\Enums;

enum DebtType: string
{
    case IOwe = 'i_owe';
    case OwedToMe = 'owed_to_me';
}
