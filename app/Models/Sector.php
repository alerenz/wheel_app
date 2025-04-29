<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Model\Wheel;

class Sector extends Model
{
    protected $fillable=[
        'name',
        'prize_type',
        'prize_id',
        'probability',
        'wheel_id',
        'count',

    ];

    public function prize()
    {
        return $this->morphTo();
    }

    public function wheel(): BelongsTo
    {
        return $this->belongsTo(Wheel::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->probability = 0;
        });
    }
}
