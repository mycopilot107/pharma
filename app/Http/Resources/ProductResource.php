<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'brand' => $this->brand,
            'strength' => $this->strength,
            'pack_size' => $this->pack_size,
            'category' => $this->category,
            'mrp' => $this->mrp !== null ? (float) $this->mrp : null,
            'unit_price' => (float) $this->unit_price,
            'description' => $this->description,
            'display_label' => $this->displayLabel(),
        ];
    }
}
