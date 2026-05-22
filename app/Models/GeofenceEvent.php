<?php

namespace App\Models;

use App\Models\Concerns\ScopesTrackingByDate;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class GeofenceEvent extends Model
{
    use ScopesTrackingByDate;

    public const TYPE_ENTER = 'enter';

    public const TYPE_EXIT = 'exit';

    protected $connection = 'mongodb';

    protected $collection = 'geofence_events';

    protected $fillable = [
        'company_id',
        'user_id',
        'customer_id',
        'visit_id',
        'event_type',
        'latitude',
        'longitude',
        'auto_triggered',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'user_id' => 'integer',
            'customer_id' => 'integer',
            'visit_id' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'auto_triggered' => 'boolean',
            'recorded_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
