<?php

namespace App\Enums;

enum VisitType: string
{
    case Doctor = 'doctor';
    case Chemist = 'chemist';
    case Hospital = 'hospital';

    public function label(): string
    {
        return match ($this) {
            self::Doctor => 'Doctor Visit',
            self::Chemist => 'Chemist Visit',
            self::Hospital => 'Hospital Visit',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Doctor => '👨‍⚕️',
            self::Chemist => '💊',
            self::Hospital => '🏥',
        };
    }
}
