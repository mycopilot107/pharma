<?php

namespace App\Models;

use App\Enums\PurchaseFrequency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPurchase extends Model
{
    protected $fillable = [
        'company_id',
        'customer_id',
        'user_id',
        'visit_id',
        'product_name',
        'quantity',
        'unit',
        'amount',
        'purchase_frequency',
        'purchased_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'amount' => 'decimal:2',
            'purchase_frequency' => PurchaseFrequency::class,
            'purchased_at' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }
}
