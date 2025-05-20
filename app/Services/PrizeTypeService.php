<?php

namespace App\Services;
use App\Models\Attempt;
use InvalidArgumentException;
use App\Models\Promocode;
use App\Models\EmptyPrize;
use App\Models\MaterialThing;


class PrizeTypeService
{
    protected static $prizeTypes = [
        'promocode' => Promocode::class,
        'material-thing' => MaterialThing::class,
        'empty-prize' => EmptyPrize::class,
        'attempt'=>Attempt::class,
    ];

    /**
     * Преобразует класс приза в строку.
     *
     * @param string $class
     * @return string|null
     */
    public static function classToString(string $class): ?string
    {
        return array_search($class, self::$prizeTypes) ?: null;
    }

    /**
     * Преобразует строку приза в класс.
     *
     * @param string $type
     * @return string
     * @throws InvalidArgumentException
     */
    public static function stringToClass(string $type): string
    {
        if (!isset(self::$prizeTypes[$type])) {
            throw new InvalidArgumentException('Некорректный тип приза');
        }
        return self::$prizeTypes[$type];
    }
}
