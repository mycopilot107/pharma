<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'customer_id',
        'visit_id',
        'order_number',
        'order_date',
        'status',
        'total_amount',
        'currency',
        'notes',
        'reviewed_by',
        'reviewed_at',
        'manager_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'order_date' => 'date',
            'total_amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatus::Pending;
    }

    public function formattedTotal(): string
    {
        return \App\Support\Currency::format($this->total_amount, $this->currency);
    }
}
