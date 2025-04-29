<?php

namespace App\Enums;

enum StatusWeelType:string
{
    case active = 'Активно';
    case nonActive = 'Не активно';
    case archive = 'В архиве';
}
