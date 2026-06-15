<?php

namespace App\Enums;

enum ExpenseType: string
{
    case Fuel          = 'fuel';
    case Food          = 'food';
    case Travel        = 'travel';
    case Hotel         = 'hotel';
    case Accommodation = 'accommodation';
    case Medical       = 'medical';
    case Communication = 'communication';
    case Other         = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Fuel          => 'Fuel',
            self::Food          => 'Food',
            self::Travel        => 'Travel',
            self::Hotel         => 'Hotel',
            self::Accommodation => 'Accommodation',
            self::Medical       => 'Medical',
            self::Communication => 'Communication',
            self::Other         => 'Other',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Fuel          => '⛽',
            self::Food          => '🍽️',
            self::Travel        => '🚌',
            self::Hotel         => '🏨',
            self::Accommodation => '🏠',
            self::Medical       => '💊',
            self::Communication => '📱',
            self::Other         => '📋',
        };
    }
}
