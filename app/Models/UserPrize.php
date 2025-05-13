<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Wheel;
use App\Models\PromocodesCode;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * 
 *
 * @property int $id
 * @property string $date
 * @property string $prize_type
 * @property int $prize_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $wheel_id
 * @property int|null $promocodeCode_id
 * @property-read Model|\Eloquent $prize
 * @property-read PromocodesCode|null $promocodeCode
 * @property-read User $user
 * @property-read Wheel $wheel
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPrize newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPrize newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPrize query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPrize whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPrize whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPrize whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPrize wherePrizeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPrize wherePrizeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPrize wherePromocodeCodeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPrize whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPrize whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPrize whereWheelId($value)
 * @mixin \Eloquent
 */
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
