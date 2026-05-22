<?php

namespace App\Enums;

enum ExpenseType: string
{
    case Fuel = 'fuel';
    case Hotel = 'hotel';
    case Food = 'food';

    public function label(): string
    {
        return match ($this) {
            self::Fuel => 'Fuel',
            self::Hotel => 'Hotel',
            self::Food => 'Food',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Fuel => '⛽',
            self::Hotel => '🏨',
            self::Food => '🍽️',
        };
    }
}
