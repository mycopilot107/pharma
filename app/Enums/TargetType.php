<?php

namespace App\Enums;

enum TargetType: string
{
    case Monthly = 'monthly';
    case Product = 'product';
    case Sales = 'sales';
    case Area = 'area';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Monthly Target',
            self::Product => 'Product Target',
            self::Sales => 'Sales Goal',
            self::Area => 'Area-wise Target',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Monthly => 'Overall monthly performance target for the MR',
            self::Product => 'Target for a specific product line or SKU',
            self::Sales => 'Revenue or sales volume goal',
            self::Area => 'Territory or geographic area assignment',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Monthly => '📅',
            self::Product => '📦',
            self::Sales => '💰',
            self::Area => '🗺️',
        };
    }
}
