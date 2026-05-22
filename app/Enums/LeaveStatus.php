<?php

namespace App\Enums;

enum LeaveStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-800',
            self::Approved => 'bg-emerald-100 text-emerald-800',
            self::Rejected => 'bg-red-100 text-red-800',
            self::Cancelled => 'bg-slate-100 text-slate-700',
        };
    }
}
