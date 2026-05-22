<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitValidation extends Model
{
    protected $fillable = [
        'visit_id',
        'risk_score',
        'flags',
        'distance_from_customer_m',
        'gps_verified',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'flags' => 'array',
            'distance_from_customer_m' => 'decimal:2',
            'gps_verified' => 'boolean',
            'validated_at' => 'datetime',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function isSuspicious(): bool
    {
        return $this->risk_score >= 50;
    }
}
