<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Wheel;

/**
 * 
 *
 * @property int $id
 * @property string $prize_type
 * @property int $prize_id
 * @property float $probability
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $wheel_id
 * @property-read Model|\Eloquent $prize
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector wherePrizeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector wherePrizeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector whereProbability($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector whereWheelId($value)
 * @property-read Wheel $wheel
 * @mixin \Eloquent
 */
class Sector extends Model
{
    protected $fillable=[
        'prize_type',
        'prize_id',
        'probability',
        'wheel_id',
    ];

    public function prize()
    {
        return $this->morphTo();
    }

    public function wheel()
    {
        return $this->belongsTo(Wheel::class);
    }

}
