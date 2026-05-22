<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-800',
            self::Confirmed => 'bg-emerald-100 text-emerald-800',
            self::Delivered => 'bg-blue-100 text-blue-800',
            self::Cancelled => 'bg-slate-100 text-slate-600',
        };
    }
}
