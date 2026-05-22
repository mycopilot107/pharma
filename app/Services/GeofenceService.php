<?php

namespace App\Services;

use App\Enums\VisitStatus;
use App\Enums\VisitType;
use App\Models\Customer;
use App\Models\GeofenceEvent;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Collection;

class GeofenceService
{
    public function __construct(
        protected GeoMathService $geo,
        protected TrackingService $tracking,
    ) {}

    public function zonesForUser(User $user): Collection
    {
        return Customer::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'name', 'type', 'latitude', 'longitude', 'geofence_radius_meters', 'geofence_auto_checkin', 'address']);
    }

    public function handleEvent(
        User $user,
        int $customerId,
        string $eventType,
        float $latitude,
        float $longitude,
    ): array {
        $customer = Customer::where('company_id', $user->company_id)
            ->where('id', $customerId)
            ->firstOrFail();

        if (! $customer->latitude || ! $customer->longitude) {
            return ['ok' => false, 'message' => 'Customer has no geofence coordinates'];
        }

        $radius = $customer->geofence_radius_meters
            ?? config('tracking.geofence_default_radius_meters', 150);

        $inside = $this->geo->isInsideGeofence(
            $latitude,
            $longitude,
            (float) $customer->latitude,
            (float) $customer->longitude,
            $radius,
        );

        if ($eventType === GeofenceEvent::TYPE_ENTER && ! $inside) {
            return ['ok' => false, 'message' => 'Not inside geofence'];
        }
        if ($eventType === GeofenceEvent::TYPE_EXIT && $inside) {
            return ['ok' => false, 'message' => 'Still inside geofence'];
        }

        $visit = null;
        $action = null;

        if ($customer->geofence_auto_checkin) {
            if ($eventType === GeofenceEvent::TYPE_ENTER) {
                [$visit, $action] = $this->autoCheckIn($user, $customer, $latitude, $longitude);
            } else {
                [$visit, $action] = $this->autoCheckOut($user, $customer, $latitude, $longitude);
            }
        }

        GeofenceEvent::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'visit_id' => $visit?->id,
            'event_type' => $eventType,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'auto_triggered' => $action !== null,
            'recorded_at' => now(),
        ]);

        $this->tracking->recordPing(
            $user,
            $latitude,
            $longitude,
            source: 'geofence_'.$eventType,
        );

        return [
            'ok' => true,
            'event' => $eventType,
            'customer' => $customer->name,
            'action' => $action,
            'visit_id' => $visit?->id,
        ];
    }

    protected function autoCheckIn(User $user, Customer $customer, float $lat, float $lng): array
    {
        $active = $user->activeVisit();
        if ($active && $active->customer_id === $customer->id) {
            return [$active, 'already_checked_in'];
        }

        if ($active) {
            return [$active, 'other_visit_active'];
        }

        $visit = Visit::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'visit_type' => $this->visitTypeForCustomer($customer),
            'place_name' => $customer->name,
            'address' => $customer->address,
            'status' => VisitStatus::InProgress,
            'planned_at' => now(),
            'checked_in_at' => now(),
            'check_in_latitude' => $lat,
            'check_in_longitude' => $lng,
            'geofence_checkin' => true,
        ]);

        return [$visit, 'auto_check_in'];
    }

    protected function visitTypeForCustomer(Customer $customer): VisitType
    {
        return match ($customer->type?->value ?? 'doctor') {
            'chemist' => VisitType::Chemist,
            'hospital' => VisitType::Hospital,
            default => VisitType::Doctor,
        };
    }

    protected function autoCheckOut(User $user, Customer $customer, float $lat, float $lng): array
    {
        $visit = Visit::where('user_id', $user->id)
            ->where('customer_id', $customer->id)
            ->where('status', VisitStatus::InProgress)
            ->latest('checked_in_at')
            ->first();

        if (! $visit) {
            return [null, 'no_active_visit'];
        }

        $checkedOut = now();
        $duration = $visit->checked_in_at
            ? (int) $visit->checked_in_at->diffInMinutes($checkedOut)
            : null;

        $visit->update([
            'status' => VisitStatus::Completed,
            'checked_out_at' => $checkedOut,
            'check_out_latitude' => $lat,
            'check_out_longitude' => $lng,
            'duration_minutes' => $duration,
        ]);

        app(VisitValidationService::class)->validateAndStore($visit->fresh());

        return [$visit->fresh(), 'auto_check_out'];
    }
}
