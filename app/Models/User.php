<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\VisitStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'company_id', 'role',
        'phone', 'is_active', 'last_latitude', 'last_longitude',
        'last_location_at', 'tracking_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role' => UserRole::class,
            'last_latitude' => 'decimal:7',
            'last_longitude' => 'decimal:7',
            'last_location_at' => 'datetime',
            'tracking_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isCompanyAdmin(): bool
    {
        return $this->role === UserRole::CompanyAdmin;
    }

    public function isRepresentative(): bool
    {
        return $this->role === UserRole::Representative;
    }

    public function visits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function dailyRoutes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DailyRoute::class);
    }

    public function targets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Target::class);
    }

    public function assignedTargets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Target::class, 'assigned_by');
    }

    public function activeVisit(): ?Visit
    {
        return $this->visits()
            ->where('status', VisitStatus::InProgress)
            ->latest('checked_in_at')
            ->first();
    }
}
