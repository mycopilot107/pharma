<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case CompanyAdmin = 'company_admin';
    case Representative = 'representative';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::CompanyAdmin => 'Company Admin',
            self::Representative => 'Medical Representative',
        };
    }
}
