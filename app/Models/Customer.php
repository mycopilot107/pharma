<?php

namespace App\Models;

use App\Enums\CustomerType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'company_id',
        'created_by',
        'type',
        'name',
        'contact_person',
        'specialty',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'pincode',
        'latitude',
        'longitude',
        'geofence_radius_meters',
        'geofence_auto_checkin',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => CustomerType::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'geofence_auto_checkin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(CustomerFollowUp::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(CustomerPrescription::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(CustomerPurchase::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function fullAddress(): string
    {
        return collect([$this->address, $this->city, $this->state, $this->pincode])
            ->filter()
            ->implode(', ');
    }
}
