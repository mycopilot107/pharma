<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'company_id',
        'plan_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'amount_usd',
        'currency',
        'status',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
