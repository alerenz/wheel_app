<?php

namespace App\Enums;

enum DiscountType: string
{
    case Percent = 'Процентная';
    case Fixed = 'Фиксированная';
};

