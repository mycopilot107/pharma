<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'sku',
        'brand',
        'strength',
        'pack_size',
        'category',
        'mrp',
        'unit_price',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'mrp' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function formattedPrice(): string
    {
        return \App\Support\Currency::format($this->unit_price);
    }

    public function displayLabel(): string
    {
        $parts = array_filter([$this->name, $this->strength, $this->pack_size]);

        return implode(' · ', $parts);
    }
}
