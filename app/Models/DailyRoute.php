<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyRoute extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'route_date',
        'title',
        'notes',
        'status',
        'started_at',
        'ended_at',
        'start_latitude',
        'start_longitude',
    ];

    protected function casts(): array
    {
        return [
            'route_date' => 'date',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'start_latitude' => 'decimal:7',
            'start_longitude' => 'decimal:7',
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

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function completedVisitsCount(): int
    {
        return $this->visits()->where('status', 'completed')->count();
    }
}
