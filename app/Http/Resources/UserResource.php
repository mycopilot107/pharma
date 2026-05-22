<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role?->value,
            'tracking_active' => (bool) $this->tracking_active,
            'company' => $this->whenLoaded('company', fn () => [
                'id' => $this->company->id,
                'name' => $this->company->name,
                'currency' => $this->company->currency,
                'currency_symbol' => $this->company->currencySymbol(),
                'plan' => $this->company->relationLoaded('plan') && $this->company->plan
                    ? ['name' => $this->company->plan->name]
                    : null,
            ]),
        ];
    }
}
