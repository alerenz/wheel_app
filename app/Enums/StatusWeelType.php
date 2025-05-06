<?php

namespace App\Enums;

// wheel
enum StatusWeelType:string
{
    case active = 'Активно';
    case nonActive = 'Не активно';
    case archive = 'В архиве';
}
