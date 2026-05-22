<?php

namespace App\Models;

use App\Enums\ReminderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'type',
        'title',
        'body',
        'action_url',
        'reference_type',
        'reference_id',
        'remind_at',
        'priority',
        'read_at',
        'dismissed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ReminderType::class,
            'remind_at' => 'datetime',
            'read_at' => 'datetime',
            'dismissed_at' => 'datetime',
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

    public function isUnread(): bool
    {
        return $this->read_at === null && $this->dismissed_at === null;
    }

    public function isDue(): bool
    {
        return $this->remind_at->lte(now());
    }

    public function markRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    public function scopeActive($query)
    {
        return $query->whereNull('dismissed_at');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at')->whereNull('dismissed_at');
    }

    public function scopeDue($query)
    {
        return $query->where('remind_at', '<=', now());
    }
}
