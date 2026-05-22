<?php

namespace App\Services;

use App\Enums\VisitStatus;
use App\Models\LocationPing;
use App\Models\Visit;
use App\Models\VisitValidation;
use Illuminate\Support\Facades\DB;

class VisitValidationService
{
    public function __construct(protected GeoMathService $geo) {}

    public function validateAndStore(Visit $visit): VisitValidation
    {
        $flags = [];
        $risk = 0;

        $minDuration = config('tracking.visit_min_duration_minutes', 3);
        $maxDistance = config('tracking.visit_max_distance_meters', 500);
        $repeatRadius = config('tracking.fraud_repeat_location_meters', 50);

        if ($visit->duration_minutes !== null && $visit->duration_minutes < $minDuration) {
            $flags[] = 'short_duration';
            $risk += 35;
        }

        if (! $visit->check_in_latitude || ! $visit->check_out_latitude) {
            $flags[] = 'missing_gps';
            $risk += 40;
        }

        $distanceFromCustomer = null;
        $gpsVerified = false;

        if ($visit->customer_id && $visit->customer) {
            $customer = $visit->customer;
            if ($customer->latitude && $customer->longitude && $visit->check_in_latitude) {
                $distanceFromCustomer = $this->geo->distanceMeters(
                    (float) $visit->check_in_latitude,
                    (float) $visit->check_in_longitude,
                    (float) $customer->latitude,
                    (float) $customer->longitude,
                );
                if ($distanceFromCustomer <= $maxDistance) {
                    $gpsVerified = true;
                } else {
                    $flags[] = 'far_from_customer';
                    $risk += 45;
                }
            }
        }

        if ($visit->check_in_latitude && $visit->check_out_latitude) {
            $checkInOutDist = $this->geo->distanceMeters(
                (float) $visit->check_in_latitude,
                (float) $visit->check_in_longitude,
                (float) $visit->check_out_latitude,
                (float) $visit->check_out_longitude,
            );
            if ($checkInOutDist < 20 && ($visit->duration_minutes ?? 0) > 5) {
                $flags[] = 'no_movement_during_visit';
                $risk += 25;
            }
        }

        $sameDayRepeats = Visit::where('user_id', $visit->user_id)
            ->where('id', '!=', $visit->id)
            ->whereDate('created_at', $visit->created_at)
            ->where('status', VisitStatus::Completed)
            ->whereNotNull('check_in_latitude')
            ->get();

        foreach ($sameDayRepeats as $other) {
            if ($visit->check_in_latitude && $other->check_in_latitude) {
                $d = $this->geo->distanceMeters(
                    (float) $visit->check_in_latitude,
                    (float) $visit->check_in_longitude,
                    (float) $other->check_in_latitude,
                    (float) $other->check_in_longitude,
                );
                if ($d < $repeatRadius) {
                    $flags[] = 'repeated_same_location';
                    $risk += 30;
                    break;
                }
            }
        }

        $pingCount = 0;
        if ($visit->checked_in_at && $visit->checked_out_at) {
            $pingCount = LocationPing::where('user_id', $visit->user_id)
                ->where('recorded_at', '>=', $visit->checked_in_at)
                ->where('recorded_at', '<=', $visit->checked_out_at)
                ->count();
            if ($pingCount === 0 && $visit->user->tracking_active) {
                $flags[] = 'no_tracking_pings_during_visit';
                $risk += 20;
            }
        }

        $risk = min(100, $risk);

        return VisitValidation::updateOrCreate(
            ['visit_id' => $visit->id],
            [
                'risk_score' => $risk,
                'flags' => array_values(array_unique($flags)),
                'distance_from_customer_m' => $distanceFromCustomer,
                'gps_verified' => $gpsVerified,
                'validated_at' => now(),
            ]
        );
    }

    public function suspiciousVisitsForCompany(int $companyId, int $limit = 20)
    {
        return VisitValidation::query()
            ->where('risk_score', '>=', 50)
            ->whereHas('visit', fn ($q) => $q->where('company_id', $companyId))
            ->with(['visit.user:id,name', 'visit.customer:id,name'])
            ->latest('validated_at')
            ->take($limit)
            ->get();
    }
}
