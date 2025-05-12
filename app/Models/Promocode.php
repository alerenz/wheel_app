<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Model\Sector;
use App\Model\UserPrize;
use App\Model\PromocodesCode;

class Promocode extends Model
{
    protected $fillable = ['name'];

    protected $casts = [
        
    ];

    public function codes(){
        return $this->hasMany(PromocodesCode::class);
    }

    public function sectors(): MorphMany
    {
        return $this->morphMany(Sector::class, 'prize');
    }

    public function userPrizes(): MorphMany
    {
        return $this->morthMany(userPrize::class, 'prize');
    }

}
