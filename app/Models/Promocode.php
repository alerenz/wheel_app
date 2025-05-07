<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\DiscountType;
use App\Model\Sector;
use App\Model\UserPrize;

class Promocode extends Model
{
    protected $fillable = [
        'code',
        'type_discount',
        'discount_value',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'discount_value' => 'float'
    ];


    public function sectors(): MorphMany
    {
        return $this->morphMany(Sector::class, 'prize');
    }

    public function userPrizes(): MorphMany
    {
        return $this->morthMany(userPrize::class, 'prize');
    }

}
