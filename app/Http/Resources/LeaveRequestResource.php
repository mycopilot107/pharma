<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'leave_type' => $this->leave_type?->value,
            'leave_type_label' => $this->leave_type?->label(),
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'days_count' => (float) $this->days_count,
            'is_half_day' => (bool) $this->is_half_day,
            'half_day_period' => $this->half_day_period,
            'reason' => $this->reason,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'manager_notes' => $this->manager_notes,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
