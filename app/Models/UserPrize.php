<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Wheel;
use App\Models\PromocodesCode;
use Tymon\JWTAuth\Facades\JWTAuth;

// для удобства можно сгенерировать аннотации к моделям через https://github.com/barryvdh/laravel-ide-helper
class UserPrize extends Model
{
    protected $fillable = [
        'prize_type',
        'prize_id',
        'date',
        'user_id',
        'wheel_id',
        'promocodeCode_id'
    ];


    public function prize()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wheel()
    {
        return $this->belongsTo(Wheel::class);
    }

    public function promocodeCode(){
        return $this->belongsTo(PromocodesCode::class);
    }

}
