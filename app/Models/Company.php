<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'plan_id',
        'user_limit',
        'currency',
        'amount_paid_usd',
        'status',
        'razorpay_order_id',
        'razorpay_payment_id',
        'subscribed_at',
        'subscription_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'user_limit' => 'integer',
            'amount_paid_usd' => 'decimal:2',
            'subscribed_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function representativesCount(): int
    {
        return $this->users()
            ->where('role', 'representative')
            ->count();
    }

    public function canAddRepresentative(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->representativesCount() < $this->user_limit;
    }

    public function remainingSlots(): int
    {
        return max(0, $this->user_limit - $this->representativesCount());
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && ($this->subscription_ends_at === null || $this->subscription_ends_at->isFuture());
    }

    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_SUSPENDED => 'Inactive',
            self::STATUS_PENDING => 'Pending',
        ];
    }

    public function accessLabel(): string
    {
        if ($this->isPending()) {
            return 'Pending';
        }

        if ($this->isInactive()) {
            return 'Inactive';
        }

        return $this->isActive() ? 'Active' : 'Expired';
    }

    public function accessBadgeClass(): string
    {
        return match ($this->accessLabel()) {
            'Active' => 'bg-emerald-100 text-emerald-800',
            'Inactive' => 'bg-slate-200 text-slate-700',
            'Pending' => 'bg-amber-100 text-amber-800',
            'Expired' => 'bg-orange-100 text-orange-800',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    public function currencySymbol(): string
    {
        return \App\Support\Currency::symbol($this->currency);
    }

    public function formatMoney(float|int|string|null $amount): string
    {
        return \App\Support\Currency::format($amount, $this->currency);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /** @deprecated Use customers() */
    public function contacts(): HasMany
    {
        return $this->customers();
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function targets(): HasMany
    {
        return $this->hasMany(Target::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
