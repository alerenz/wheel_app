<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Model\Sector;


class Empty_prize extends Model
{
    protected $fillable=['name'];

    public function sectors(): MorphMany
    {
        return $this->morphMany(Sector::class, 'prize');
    }
}
