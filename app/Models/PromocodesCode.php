<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Promocode;
use App\Models\UserPrize;

/**
 * 
 *
 * @property int $id
 * @property string $code
 * @property bool $active
 * @property int $promocode_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Promocode $promocode
 * @property-read UserPrize|null $userPrize
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromocodesCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromocodesCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromocodesCode query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromocodesCode whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromocodesCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromocodesCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromocodesCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromocodesCode wherePromocodeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromocodesCode whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
