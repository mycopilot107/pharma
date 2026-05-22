<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPrescription extends Model
{
    protected $fillable = [
        'company_id',
        'customer_id',
        'user_id',
        'visit_id',
        'product_name',
        'brand',
        'strength',
        'quantity',
        'unit',
        'prescribed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'prescribed_at' => 'date',
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

    public function formattedQuantity(): string
    {
        $qty = rtrim(rtrim(number_format((float) $this->quantity, 2), '0'), '.');

        return $this->unit ? "{$qty} {$this->unit}" : $qty;
    }
}
