<?php

namespace App\Enums;

enum LeaveType: string
{
    case Casual = 'casual';
    case Sick = 'sick';
    case Annual = 'annual';
    case Unpaid = 'unpaid';
    case Emergency = 'emergency';

    public function label(): string
    {
        return match ($this) {
            self::Casual => 'Casual Leave',
            self::Sick => 'Sick Leave',
            self::Annual => 'Annual Leave',
            self::Unpaid => 'Unpaid Leave',
            self::Emergency => 'Emergency Leave',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Casual => '📅',
            self::Sick => '🤒',
            self::Annual => '🏖️',
            self::Unpaid => '📋',
            self::Emergency => '🚨',
        };
    }

    public function hasBalance(): bool
    {
        return $this !== self::Unpaid;
    }
}
