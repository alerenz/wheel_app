<?php

namespace App\Services;

use App\Enums\StatusWheelType;
use App\Models\Wheel;

class ActiveWheelService
{
    /**
     * Create a new class instance.
     */
    public static function getActiveWheel()
    {
        $wheel = Wheel::with(['sectors.prize'])
            ->where('status', StatusWheelType::active->value)
            ->orderBy('created_at', 'desc')
            ->first();
        if(!$wheel){
            return null;
        }

        return $wheel;
    }
}
