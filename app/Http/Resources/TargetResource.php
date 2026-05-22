<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TargetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $progress = $this->target_value > 0
            ? min(100, round(($this->achieved_value / $this->target_value) * 100, 1))
            : 0;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type?->value ?? $this->type,
            'unit' => $this->unit?->value ?? $this->unit,
            'target_value' => (float) $this->target_value,
            'achieved_value' => (float) $this->achieved_value,
            'progress_percent' => $progress,
            'period_start' => $this->period_start?->toDateString(),
            'period_end' => $this->period_end?->toDateString(),
            'status' => $this->status,
            'description' => $this->description,
        ];
    }
}
