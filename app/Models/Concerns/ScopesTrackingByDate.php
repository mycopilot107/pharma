<?php

namespace App\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait ScopesTrackingByDate
{
    public function scopeForDate(Builder $query, Carbon $date): Builder
    {
        return $query
            ->where('recorded_at', '>=', $date->copy()->startOfDay())
            ->where('recorded_at', '<=', $date->copy()->endOfDay());
    }
}
