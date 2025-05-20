<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Sector;
use App\Models\UserPrize;


/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmptyPrize newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmptyPrize newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmptyPrize query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmptyPrize whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmptyPrize whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmptyPrize whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmptyPrize whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Sector> $sectors
 * @property-read int|null $sectors_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserPrize> $userPrizes
 * @property-read int|null $user_prizes_count
 * @mixin \Eloquent
 */
class EmptyPrize extends Model
{
    protected $fillable=['name'];

    public function sectors()
    {
        return $this->morphMany(Sector::class, 'prize');
    }

        public function userPrizes()
    {
        return $this->morphMany(UserPrize::class, 'prize');
    }
}
