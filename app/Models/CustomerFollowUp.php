<?php

namespace App\Models;

use App\Enums\FollowUpStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerFollowUp extends Model
{
    protected $fillable = [
        'company_id',
        'customer_id',
        'user_id',
        'visit_id',
        'title',
        'notes',
        'due_at',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => FollowUpStatus::class,
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function isOverdue(): bool
    {
        return $this->status === FollowUpStatus::Pending && $this->due_at->isPast();
    }
}
