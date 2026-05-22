<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type?->value,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'expense_date' => $this->expense_date?->toDateString(),
            'description' => $this->description,
            'status' => $this->status?->value,
            'receipt_url' => $this->receiptUrl(),
            'review_notes' => $this->when($this->status?->value !== 'pending', $this->review_notes),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
