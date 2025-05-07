<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Model\Sector;


class EmptyPrize extends Model
{
    protected $fillable=['name'];

    public function sectors(): MorphMany
    {
        return $this->morphMany(Sector::class, 'prize');
    }
}
