<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type?->value,
            'name' => $this->name,
            'contact_person' => $this->contact_person,
            'specialty' => $this->specialty,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'pincode' => $this->pincode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'notes' => $this->notes,
        ];
    }
}
