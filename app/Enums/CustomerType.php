<?php

namespace App\Enums;

enum CustomerType: string
{
    case Doctor = 'doctor';
    case Hospital = 'hospital';
    case Clinic = 'clinic';
    case Chemist = 'chemist';
    case Distributor = 'distributor';

    public function label(): string
    {
        return match ($this) {
            self::Doctor => 'Doctor',
            self::Hospital => 'Hospital',
            self::Clinic => 'Clinic',
            self::Chemist => 'Chemist',
            self::Distributor => 'Distributor',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Doctor => '👨‍⚕️',
            self::Hospital => '🏥',
            self::Clinic => '🏨',
            self::Chemist => '💊',
            self::Distributor => '🚚',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Doctor => 'bg-blue-100 text-blue-800',
            self::Hospital => 'bg-red-100 text-red-800',
            self::Clinic => 'bg-purple-100 text-purple-800',
            self::Chemist => 'bg-green-100 text-green-800',
            self::Distributor => 'bg-orange-100 text-orange-800',
        };
    }
}
