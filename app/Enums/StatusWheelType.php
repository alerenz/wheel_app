<?php

namespace App\Enums;

enum StatusWheelType:string
{
    case active = 'Активно';
    case nonActive = 'Не активно';
    case archive = 'Архив';
}
