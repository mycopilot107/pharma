<?php

namespace App\Models;

use App\Models\Concerns\ScopesTrackingByDate;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class LocationPing extends Model
{
    use ScopesTrackingByDate;

    protected $connection = 'mongodb';

    protected $collection = 'location_pings';

    protected $fillable = [
        'company_id',
        'user_id',
        'daily_route_id',
        'latitude',
        'longitude',
        'accuracy',
        'speed',
        'heading',
        'altitude',
        'battery_percent',
        'is_background',
        'source',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'user_id' => 'integer',
            'daily_route_id' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'accuracy' => 'float',
            'speed' => 'float',
            'heading' => 'float',
            'altitude' => 'float',
            'battery_percent' => 'integer',
            'is_background' => 'boolean',
            'recorded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
