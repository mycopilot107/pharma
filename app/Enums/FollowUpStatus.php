<?php

namespace App\Enums;

enum FollowUpStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-800',
            self::Completed => 'bg-emerald-100 text-emerald-800',
            self::Cancelled => 'bg-slate-100 text-slate-600',
        };
    }
}
