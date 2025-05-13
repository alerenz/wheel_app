<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Model\Sector;
use App\Model\UserPrize;

class MaterialThing extends Model
{
    protected $fillable = [
        'name',
    ];


    public function sectors(): MorphMany
    {
        return $this->morphMany(Sector::class, 'prize');
    }

    public function userPrizes(): MorphMany
    {
        return $this->morphMany(Sector::class, 'prize');
    }
}
