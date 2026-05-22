<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MrAttendance extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'company_id',
        'user_id',
        'work_date',
        'clock_in_at',
        'clock_out_at',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_latitude',
        'clock_out_longitude',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'clock_in_at' => 'datetime',
            'clock_out_at' => 'datetime',
            'clock_in_latitude' => 'decimal:7',
            'clock_in_longitude' => 'decimal:7',
            'clock_out_latitude' => 'decimal:7',
            'clock_out_longitude' => 'decimal:7',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->clock_out_at === null;
    }

    public function durationMinutes(): ?int
    {
        $end = $this->clock_out_at ?? now();

        return (int) $this->clock_in_at->diffInMinutes($end);
    }
}
