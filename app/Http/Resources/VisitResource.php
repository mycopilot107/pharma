<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visit_type' => $this->visit_type?->value,
            'place_name' => $this->place_name,
            'address' => $this->address,
            'status' => $this->status?->value,
            'planned_at' => $this->planned_at?->toIso8601String(),
            'checked_in_at' => $this->checked_in_at?->toIso8601String(),
            'checked_out_at' => $this->checked_out_at?->toIso8601String(),
            'check_in_latitude' => $this->check_in_latitude,
            'check_in_longitude' => $this->check_in_longitude,
            'check_out_latitude' => $this->check_out_latitude,
            'check_out_longitude' => $this->check_out_longitude,
            'duration_minutes' => $this->duration_minutes,
            'notes' => $this->notes,
            'ai_summary' => $this->ai_summary,
            'customer' => $this->whenLoaded('customer', fn () => new CustomerResource($this->customer)),
            'photos' => $this->whenLoaded('photos', fn () => $this->photos->map(fn ($p) => [
                'id' => $p->id,
                'url' => $p->url(),
                'caption' => $p->caption,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
