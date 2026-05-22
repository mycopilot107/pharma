<?php

namespace App\Enums;

enum TargetUnit: string
{
    case Currency = 'currency';
    case Units = 'units';
    case Visits = 'visits';
    case Percentage = 'percentage';

    public function label(): string
    {
        return match ($this) {
            self::Currency => 'USD ($)',
            self::Units => 'Units / Qty',
            self::Visits => 'Visits',
            self::Percentage => 'Percentage (%)',
        };
    }

    public function formatValue(float $value): string
    {
        return match ($this) {
            self::Currency => '$'.number_format($value, 2),
            self::Units => number_format($value, 0).' units',
            self::Visits => number_format($value, 0).' visits',
            self::Percentage => number_format($value, 1).'%',
        };
    }
}
