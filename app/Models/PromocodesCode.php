<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Promocode;
use App\Models\UserPrize;

class PromocodesCode extends Model
{
    protected $fillable=[
        'code',
        'active',
        'promocode_id'
    ];

    public function promocode(){
        return $this->belongsTo(Promocode::class);
    }

    public function userPrize(){
        return $this->hasOne(UserPrize::class);
    }
}
