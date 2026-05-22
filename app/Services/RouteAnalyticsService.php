<?php

namespace App\Services;

use Illuminate\Support\Collection;

class RouteAnalyticsService
{
    public function __construct(protected GeoMathService $geo) {}

    public function analyze(Collection $pings): array
    {
        if ($pings->isEmpty()) {
            return [
                'distance_km' => 0,
                'ping_count' => 0,
                'stops' => [],
                'moving_minutes' => 0,
                'idle_minutes' => 0,
            ];
        }

        $dwellMinutes = config('tracking.dwell_stop_minutes', 5);
        $totalMeters = 0;
        $stops = [];
        $stopStart = null;
        $stopLat = null;
        $stopLng = null;

        $sorted = $pings->sortBy('recorded_at')->values();
        $prev = null;

        foreach ($sorted as $ping) {
            $lat = (float) $ping->latitude;
            $lng = (float) $ping->longitude;

            if ($prev) {
                $dist = $this->geo->distanceMeters(
                    (float) $prev->latitude,
                    (float) $prev->longitude,
                    $lat,
                    $lng,
                );
                $totalMeters += $dist;

                if ($dist < 30) {
                    if ($stopStart === null) {
                        $stopStart = $prev->recorded_at;
                        $stopLat = (float) $prev->latitude;
                        $stopLng = (float) $prev->longitude;
                    }
                } elseif ($stopStart !== null) {
                    $mins = $stopStart->diffInMinutes($prev->recorded_at);
                    if ($mins >= $dwellMinutes) {
                        $stops[] = [
                            'lat' => $stopLat,
                            'lng' => $stopLng,
                            'from' => $stopStart->format('H:i'),
                            'to' => $prev->recorded_at->format('H:i'),
                            'duration_minutes' => $mins,
                        ];
                    }
                    $stopStart = null;
                }
            }
            $prev = $ping;
        }

        $firstAt = $sorted->first()->recorded_at;
        $lastAt = $sorted->last()->recorded_at;
        $totalMinutes = max(1, $firstAt->diffInMinutes($lastAt));
        $stopMinutes = collect($stops)->sum('duration_minutes');

        return [
            'distance_km' => round($totalMeters / 1000, 2),
            'ping_count' => $sorted->count(),
            'stops' => $stops,
            'moving_minutes' => max(0, $totalMinutes - $stopMinutes),
            'idle_minutes' => $stopMinutes,
            'started_at' => $firstAt->format('H:i'),
            'ended_at' => $lastAt->format('H:i'),
        ];
    }
}
