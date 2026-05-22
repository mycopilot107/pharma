<?php

namespace App\Services;

class GeoMathService
{
    public function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earthRadius * asin(min(1, sqrt($a)));
    }

    public function isInsideGeofence(
        float $lat,
        float $lng,
        float $centerLat,
        float $centerLng,
        int $radiusMeters,
    ): bool {
        return $this->distanceMeters($lat, $lng, $centerLat, $centerLng) <= $radiusMeters;
    }
}
