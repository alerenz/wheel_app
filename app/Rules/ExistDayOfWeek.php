<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExistDayOfWeek implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */

    private $validDays = ['понедельник', 'вторник','среда','четверг','пятница','суббота','воскресенье'];

    // Конструктор для передачи prize_type

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            $fail("Поле {$attribute} должно быть массивом.");
            return;
        }

        foreach ($value as $day) {
            $day = mb_strtolower($day);
            if (!in_array($day, $this->validDays)) {
                $fail("День '{$day}' не является допустимым. Допустимые дни: " . implode(', ', $this->validDays) . ".");
            }
        }
    }
}
