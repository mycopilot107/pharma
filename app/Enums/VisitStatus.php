<?php

namespace App\Enums;

enum VisitStatus: string
{
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planned',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Planned => 'bg-blue-100 text-blue-800',
            self::InProgress => 'bg-amber-100 text-amber-800',
            self::Completed => 'bg-emerald-100 text-emerald-800',
            self::Cancelled => 'bg-slate-100 text-slate-600',
        };
    }
}
