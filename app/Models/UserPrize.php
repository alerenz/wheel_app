<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserPrize extends Model
{
    protected $fillable = [
        'prize_type',
        'prize_id',
        'date',
        'user_id',
        'extradition'
    ];


    public function prize()
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
}
