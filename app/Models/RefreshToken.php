<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $token
 * @property int $user_id
 * @property string $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefreshToken whereUserId($value)
 * @mixin \Eloquent
 */
class RefreshToken extends Model
{
    protected $fillable = [
        'token',
        'user_id',
        'expires_at',
    ];
    
}
