<?php

namespace App\Models;

use App\Enums\TargetType;
use App\Enums\TargetUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Target extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'company_id',
        'user_id',
        'assigned_by',
        'type',
        'title',
        'description',
        'period_start',
        'period_end',
        'product_name',
        'area_name',
        'target_value',
        'achieved_value',
        'unit',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => TargetType::class,
            'unit' => TargetUnit::class,
            'period_start' => 'date',
            'period_end' => 'date',
            'target_value' => 'decimal:2',
            'achieved_value' => 'decimal:2',
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

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function progressPercent(): float
    {
        if ((float) $this->target_value <= 0) {
            return 0;
        }

        return min(100, round(((float) $this->achieved_value / (float) $this->target_value) * 100, 1));
    }

    public function formattedTarget(): string
    {
        return $this->unit->formatValue((float) $this->target_value);
    }

    public function formattedAchieved(): string
    {
        return $this->unit->formatValue((float) $this->achieved_value);
    }

    public function periodLabel(): ?string
    {
        if (! $this->period_start) {
            return null;
        }

        if ($this->period_end && ! $this->period_start->equalTo($this->period_end)) {
            return $this->period_start->format('d M Y').' – '.$this->period_end->format('d M Y');
        }

        return $this->period_start->format('F Y');
    }

    public function subtitle(): string
    {
        return match ($this->type) {
            TargetType::Product => $this->product_name ?? '—',
            TargetType::Area => $this->area_name ?? '—',
            TargetType::Monthly => $this->periodLabel() ?? 'Monthly',
            TargetType::Sales => $this->periodLabel() ?? 'Sales period',
        };
    }
}
