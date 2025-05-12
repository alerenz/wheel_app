<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Sector;
use App\Models\UserPrize;
use App\Enums\StatusWheelType;

class Wheel extends Model
{
    protected $fillable = [
        'name',
        'count_sectors',
        'status',
        'animation',
        'date_start',
        'date_end',
        'days_of_week',
    ];


    public function sectors()
    {
        return $this->hasMany(Sector::class);
    }

    public function userPrizes(){
        return $this->hasMany(UserPrize::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->status = StatusWheelType::nonActive;
        });
    }
}
