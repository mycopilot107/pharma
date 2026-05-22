<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'order_date' => $this->order_date?->toDateString(),
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'total_amount' => (float) $this->total_amount,
            'currency' => $this->currency,
            'notes' => $this->notes,
            'manager_notes' => $this->when($this->manager_notes, $this->manager_notes),
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'type' => $this->customer->type?->value,
                'type_label' => $this->customer->type?->label(),
            ]),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
