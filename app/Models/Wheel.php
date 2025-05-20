<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Sector;
use App\Models\UserPrize;
use App\Enums\StatusWheelType;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property bool $animation
 * @property string $date_start
 * @property string $date_end
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed> $days_of_week
 * @property string $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Sector> $sectors
 * @property-read int|null $sectors_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserPrize> $userPrizes
 * @property-read int|null $user_prizes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wheel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wheel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wheel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wheel whereAnimation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wheel whereCountSectors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wheel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wheel whereDateEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wheel whereDateStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wheel whereDaysOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wheel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wheel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wheel whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wheel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Wheel extends Model
{
    protected $fillable = [
        'name',
        'status',
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

    protected function casts(): array
    {
        return [
            'days_of_week' => 'array',
        ];
    }

}
