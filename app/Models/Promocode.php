<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Sector;
use App\Models\UserPrize;
use App\Models\PromocodesCode;

/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PromocodesCode> $codes
 * @property-read int|null $codes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocode query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocode whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocode whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Sector> $sectors
 * @property-read int|null $sectors_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserPrize> $userPrizes
 * @property-read int|null $user_prizes_count
 * @mixin \Eloquent
 */
class Promocode extends Model
{
    protected $fillable = ['name'];

    protected $casts = [
        
    ];

    public function codes(){
        return $this->hasMany(PromocodesCode::class);
    }

    public function sectors()
    {
        return $this->morphMany(Sector::class, 'prize');
    }

    public function userPrizes()
    {
        return $this->morphMany(userPrize::class, 'prize');
    }

}
