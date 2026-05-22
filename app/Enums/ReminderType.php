<?php

namespace App\Enums;

enum ReminderType: string
{
    case FollowUp = 'follow_up';
    case Meeting = 'meeting_reminder';
    case TargetAlert = 'target_alert';
    case DoctorRevisit = 'doctor_revisit';

    public function label(): string
    {
        return match ($this) {
            self::FollowUp => 'Follow-up',
            self::Meeting => 'Meeting',
            self::TargetAlert => 'Target alert',
            self::DoctorRevisit => 'Doctor revisit',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::FollowUp => '📅',
            self::Meeting => '🤝',
            self::TargetAlert => '🎯',
            self::DoctorRevisit => '👨‍⚕️',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::FollowUp => 'border-amber-200 bg-amber-50',
            self::Meeting => 'border-teal-200 bg-teal-50',
            self::TargetAlert => 'border-indigo-200 bg-indigo-50',
            self::DoctorRevisit => 'border-blue-200 bg-blue-50',
        };
    }
}
