<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\DiscountType;
use App\Model\Sector;
use App\Model\UserPrize;

class Promocode extends Model
{
    protected $fillable = [
        'type_discount',
        'discount_value',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'discount_value' => 'float'
    ];

    public function generateCode(): string
    {
        $digits = str_split('0123456789');
        $letters = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        
        do {
            $code = '';
            $allSymbols = array_merge($digits, $letters);
            
            for ($i = 0; $i < 6; $i++) {
                $code .= $allSymbols[array_rand($allSymbols)];
            }
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->code) {
                $model->code = $model->generateCode();
            }
        });
    }


    public function sectors(): MorphMany
    {
        return $this->morphMany(Sector::class, 'prize');
    }

    public function userPrizes(): MorphMany
    {
        return $this->morthMany(userPrize::class, 'prize');
    }

}
