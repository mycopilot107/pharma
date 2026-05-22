<?php

namespace App\Enums;

enum PurchaseFrequency: string
{
    case Weekly = 'weekly';
    case Biweekly = 'biweekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Irregular = 'irregular';

    public function label(): string
    {
        return match ($this) {
            self::Weekly => 'Weekly',
            self::Biweekly => 'Bi-weekly',
            self::Monthly => 'Monthly',
            self::Quarterly => 'Quarterly',
            self::Irregular => 'Irregular',
        };
    }
}
