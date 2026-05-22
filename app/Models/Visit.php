<?php

namespace App\Models;

use App\Enums\VisitStatus;
use App\Enums\VisitType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'daily_route_id',
        'customer_id',
        'visit_type',
        'place_name',
        'address',
        'status',
        'planned_at',
        'checked_in_at',
        'checked_out_at',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'duration_minutes',
        'notes',
        'ai_summary',
        'geofence_checkin',
    ];

    protected function casts(): array
    {
        return [
            'visit_type' => VisitType::class,
            'status' => VisitStatus::class,
            'planned_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'check_in_latitude' => 'decimal:7',
            'check_in_longitude' => 'decimal:7',
            'check_out_latitude' => 'decimal:7',
            'check_out_longitude' => 'decimal:7',
            'duration_minutes' => 'integer',
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

    public function dailyRoute(): BelongsTo
    {
        return $this->belongsTo(DailyRoute::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @deprecated Use customer() */
    public function contact(): BelongsTo
    {
        return $this->customer();
    }

    public function photos(): HasMany
    {
        return $this->hasMany(VisitPhoto::class);
    }

    public function validation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(VisitValidation::class);
    }

    public function isInProgress(): bool
    {
        return $this->status === VisitStatus::InProgress;
    }

    public function formattedDuration(): ?string
    {
        if ($this->duration_minutes === null) {
            return null;
        }

        if ($this->duration_minutes < 60) {
            return $this->duration_minutes.' min';
        }

        $hours = intdiv($this->duration_minutes, 60);
        $mins = $this->duration_minutes % 60;

        return $hours.'h '.$mins.'m';
    }
}
