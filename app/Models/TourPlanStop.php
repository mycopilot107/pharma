<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourPlanStop extends Model
{
    protected $fillable = [
        'tour_plan_id', 'customer_id', 'day_of_week', 'sort_order', 'area', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'sort_order'  => 'integer',
        ];
    }

    public function tourPlan(): BelongsTo { return $this->belongsTo(TourPlan::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }

    public function dayName(): string
    {
        return ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$this->day_of_week] ?? '—';
    }
}
