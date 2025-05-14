<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ExistIdPrize implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */

    protected $prizeType;
    public function __construct($prizeType)
    {
        $this->prizeType = $prizeType;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        switch ($this->prizeType) {
            case 'promocode':
                if (!DB::table('promocodes')->where('id', $value)->exists()) {
                    $fail("Приза с таким ID не существует для типа промокод.");
                }
                break;
            case 'material_thing':
                if (!DB::table('material_things')->where('id', $value)->exists()) {
                    $fail("Приза с таким ID не существует для типа вещь.");
                }
                break;
            case 'empty_prize':
                if (!DB::table('empty_prizes')->where('id', $value)->exists()) {
                    $fail("Приза с таким ID не существует для типа пустой приз.");
                }
                break;
            case 'attempt':
                if (!DB::table('attempts')->where('id', $value)->exists()) {
                    $fail("Приза с таким ID не существует для типа попытка.");
                }
                break;    
            default:
                $fail('Некорректный тип приза.');
                break;
        }
    }
}
