<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'user_limit',
        'price_usd',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'user_limit' => 'integer',
            'price_usd' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function isFree(): bool
    {
        return (float) $this->price_usd <= 0;
    }

    public function formattedPrice(?string $currencyCode = null): string
    {
        if ($this->isFree()) {
            return 'Free';
        }

        return \App\Support\Currency::format($this->price_usd, $currencyCode);
    }

    /** Razorpay amount in smallest currency unit (USD cents). */
    public function amountCents(): int
    {
        return (int) round((float) $this->price_usd * 100);
    }
}
