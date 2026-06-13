<?php

namespace App\Services;

use App\Enums\VisitStatus;
use App\Models\Customer;
use App\Models\DailyRoute;
use App\Models\GeofenceEvent;
use App\Models\LocationPing;
use App\Models\MrAttendance;
use App\Models\User;
use App\Models\Visit;
use App\Services\RouteAnalyticsService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class TrackingService
{
    public function recordPing(
        User $user,
        float $latitude,
        float $longitude,
        ?float $accuracy = null,
        string $source = 'ping',
        ?int $dailyRouteId = null,
        ?float $speed = null,
        ?float $heading = null,
        ?float $altitude = null,
        ?int $batteryPercent = null,
        bool $isBackground = false,
    ): ?LocationPing {
        // Update MySQL FIRST — keeps the live-map dot current even if MongoDB is down.
        $user->update([
            'last_latitude' => $latitude,
            'last_longitude' => $longitude,
            'last_location_at' => now(),
        ]);

        // Write to Redis sorted set for live map (score = unix ms, value = "lat,lng")
        try {
            $redisKey = "track:{$user->company_id}:{$user->id}:" . now()->toDateString();
            Redis::zadd($redisKey, now()->valueOf(), "{$latitude},{$longitude}");
            Redis::expire($redisKey, 172800); // 48 hours TTL
        } catch (\Throwable $e) {
            \Log::warning('Redis ping write failed: ' . $e->getMessage());
        }

        try {
            return LocationPing::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'daily_route_id' => $dailyRouteId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy' => $accuracy,
                'speed' => $speed,
                'heading' => $heading,
                'altitude' => $altitude,
                'battery_percent' => $batteryPercent,
                'is_background' => $isBackground,
                'source' => $source,
                'recorded_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('LocationPing write failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'source' => $source,
            ]);
            return null;
        }
    }

    public function clockIn(User $user, float $latitude, float $longitude): MrAttendance
    {
        $today = now()->toDateString();

        // Close any attendance that was left active from a previous day (e.g. app crash, missed clock-out)
        MrAttendance::where('user_id', $user->id)
            ->where('work_date', '<', $today)
            ->where('status', MrAttendance::STATUS_ACTIVE)
            ->update(['status' => MrAttendance::STATUS_COMPLETED]);

        $existing = MrAttendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if ($existing?->isActive()) {
            return $existing;
        }

        if ($existing) {
            $existing->update([
                'clock_in_at' => now(),
                'clock_in_latitude' => $latitude,
                'clock_in_longitude' => $longitude,
                'clock_out_at' => null,
                'clock_out_latitude' => null,
                'clock_out_longitude' => null,
                'status' => MrAttendance::STATUS_ACTIVE,
            ]);
            $attendance = $existing->fresh();
        } else {
            $attendance = MrAttendance::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'work_date' => $today,
                'clock_in_at' => now(),
                'clock_in_latitude' => $latitude,
                'clock_in_longitude' => $longitude,
                'status' => MrAttendance::STATUS_ACTIVE,
            ]);
        }

        $user->update(['tracking_active' => true]);
        $this->recordPing($user, $latitude, $longitude, source: 'clock_in');

        return $attendance;
    }

    public function clockOut(User $user, float $latitude, float $longitude): MrAttendance
    {
        $attendance = MrAttendance::where('user_id', $user->id)
            ->where('work_date', now()->toDateString())
            ->where('status', MrAttendance::STATUS_ACTIVE)
            ->first();

        if (! $attendance) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('No active clock-in found for today.');
        }

        $attendance->update([
            'clock_out_at' => now(),
            'clock_out_latitude' => $latitude,
            'clock_out_longitude' => $longitude,
            'status' => MrAttendance::STATUS_COMPLETED,
        ]);

        $user->update(['tracking_active' => false]);
        $this->recordPing($user, $latitude, $longitude, source: 'clock_out');

        DailyRoute::where('user_id', $user->id)
            ->whereDate('route_date', now())
            ->where('status', 'in_progress')
            ->update(['status' => 'completed', 'ended_at' => now()]);

        return $attendance->fresh();
    }

    public function liveRepresentatives(int $companyId): Collection
    {
        $staleCutoff = now()->subMinutes(config('tracking.live_stale_minutes'));

        return User::where('company_id', $companyId)
            ->where('role', 'representative')
            ->where('is_active', true)
            ->get()
            ->map(function (User $user) use ($staleCutoff) {
                $attendance = MrAttendance::where('user_id', $user->id)
                    ->where('work_date', today())
                    ->first();

                $activeVisit = Visit::where('user_id', $user->id)
                    ->where('status', VisitStatus::InProgress)
                    ->with('customer:id,name')
                    ->first();

                $todayRoute = DailyRoute::where('user_id', $user->id)
                    ->whereDate('route_date', today())
                    ->first();

                $todayVisits = Visit::where('user_id', $user->id)
                    ->whereDate('created_at', today());

                $isLive = $user->last_location_at && $user->last_location_at->gte($staleCutoff);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'latitude' => $user->last_latitude ? (float) $user->last_latitude : null,
                    'longitude' => $user->last_longitude ? (float) $user->last_longitude : null,
                    'last_location_at' => $user->last_location_at?->toIso8601String(),
                    'tracking_active' => (bool) $user->tracking_active,
                    'is_live' => $isLive,
                    'status' => $this->resolveStatusLabel($user, $attendance, $activeVisit, $isLive),
                    'status_color' => $this->resolveStatusColor($user, $attendance, $activeVisit, $isLive),
                    'attendance' => $attendance ? [
                        'clock_in' => $attendance->clock_in_at->format('H:i'),
                        'clock_out' => $attendance->clock_out_at?->format('H:i'),
                        'active' => $attendance->isActive(),
                    ] : null,
                    'active_visit' => $activeVisit ? [
                        'place' => $activeVisit->place_name,
                        'customer' => $activeVisit->customer?->name,
                        'since' => $activeVisit->checked_in_at?->format('H:i'),
                    ] : null,
                    'visits_today' => [
                        'total' => $todayVisits->count(),
                        'completed' => (clone $todayVisits)->where('status', VisitStatus::Completed)->count(),
                        'in_progress' => (clone $todayVisits)->where('status', VisitStatus::InProgress)->count(),
                    ],
                    'route' => $todayRoute ? [
                        'title' => $todayRoute->title,
                        'status' => $todayRoute->status,
                    ] : null,
                ];
            });
    }

    public function routeHistory(int $companyId, int $userId, Carbon $date): array
    {
        // MongoDB queries — isolated in try/catch so a DB outage doesn't crash the whole response
        try {
            $pings = LocationPing::where('company_id', $companyId)
                ->where('user_id', $userId)
                ->forDate($date)
                ->orderBy('recorded_at')
                ->get();
        } catch (\Throwable $e) {
            \Log::warning('LocationPing query failed: ' . $e->getMessage());
            $pings = collect();
        }

        $analytics = app(RouteAnalyticsService::class)->analyze($pings);

        $visits = Visit::where('company_id', $companyId)
            ->where('user_id', $userId)
            ->whereDate('created_at', $date)
            ->with(['customer:id,name', 'validation'])
            ->orderBy('checked_in_at')
            ->get();

        $attendance = MrAttendance::where('user_id', $userId)
            ->where('work_date', $date)
            ->first();

        $route = DailyRoute::where('user_id', $userId)
            ->whereDate('route_date', $date)
            ->first();

        try {
            $geofenceEvents = GeofenceEvent::where('company_id', $companyId)
                ->where('user_id', $userId)
                ->forDate($date)
                ->orderBy('recorded_at')
                ->get();
        } catch (\Throwable $e) {
            \Log::warning('GeofenceEvent query failed: ' . $e->getMessage());
            $geofenceEvents = collect();
        }

        $customerNames = $geofenceEvents->isNotEmpty()
            ? Customer::whereIn('id', $geofenceEvents->pluck('customer_id')->filter()->unique())->pluck('name', 'id')
            : collect();

        return [
            'pings' => $pings->map(fn ($p) => [
                'latitude' => (float) $p->latitude,
                'longitude' => (float) $p->longitude,
                'created_at' => $p->recorded_at?->toIso8601String(),
                'source' => $p->source,
                'speed' => $p->speed ? (float) $p->speed : null,
                'accuracy' => $p->accuracy ? (float) $p->accuracy : null,
                'is_background' => (bool) $p->is_background,
            ])->values(),
            'analytics' => $analytics,
            'visits' => $visits->map(fn ($v) => [
                'id' => $v->id,
                'place' => $v->place_name,
                'status' => $v->status->value,
                'customer' => $v->customer?->name,
                'duration_minutes' => $v->duration_minutes,
                'geofence_checkin' => (bool) $v->geofence_checkin,
                'validation' => $v->validation ? [
                    'risk_score' => $v->validation->risk_score,
                    'flags' => $v->validation->flags,
                    'gps_verified' => $v->validation->gps_verified,
                ] : null,
                'check_in' => $v->check_in_latitude !== null ? ['lat' => (float) $v->check_in_latitude, 'lng' => (float) $v->check_in_longitude] : null,
                'check_out' => $v->check_out_latitude !== null ? ['lat' => (float) $v->check_out_latitude, 'lng' => (float) $v->check_out_longitude] : null,
            ])->values(),
            'geofence_events' => $geofenceEvents->map(fn ($e) => [
                'customer' => $customerNames[$e->customer_id] ?? null,
                'type' => $e->event_type,
                'at' => $e->recorded_at?->format('H:i:s'),
                'auto' => $e->auto_triggered,
            ])->values(),
            'attendance' => $attendance ? [
                'clock_in' => $attendance->clock_in_at?->format('H:i'),
                'clock_out' => $attendance->clock_out_at?->format('H:i'),
                'working_minutes' => $attendance->clock_out_at
                    ? $attendance->clock_in_at->diffInMinutes($attendance->clock_out_at)
                    : ($attendance->isActive() ? $attendance->clock_in_at->diffInMinutes(now()) : null),
            ] : null,
            'route' => $route?->only(['id', 'title', 'status', 'route_date']),
        ];
    }

    public function recordPingBatch(User $user, array $pings): int
    {
        if (count($pings) === 0) {
            return 0;
        }

        $routeId = DailyRoute::where('user_id', $user->id)
            ->whereDate('route_date', today())
            ->whereIn('status', ['planned', 'in_progress'])
            ->value('id');

        $now = now();
        $documents = [];
        $lastLat = null;
        $lastLng = null;

        foreach ($pings as $data) {
            $lat = (float) $data['latitude'];
            $lng = (float) $data['longitude'];
            $lastLat = $lat;
            $lastLng = $lng;

            $documents[] = [
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'daily_route_id' => $routeId,
                'latitude' => $lat,
                'longitude' => $lng,
                'accuracy' => isset($data['accuracy']) ? (float) $data['accuracy'] : null,
                'speed' => isset($data['speed']) ? (float) $data['speed'] : null,
                'heading' => isset($data['heading']) ? (float) $data['heading'] : null,
                'altitude' => isset($data['altitude']) ? (float) $data['altitude'] : null,
                'battery_percent' => isset($data['battery_percent']) ? (int) $data['battery_percent'] : null,
                'is_background' => (bool) ($data['is_background'] ?? false),
                'source' => $data['source'] ?? 'ping_batch',
                'recorded_at' => isset($data['recorded_at'])
                    ? Carbon::parse($data['recorded_at'])
                    : $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Write batch to Redis sorted set
        try {
            $redisKey = "track:{$user->company_id}:{$user->id}:" . $now->toDateString();
            $zaddArgs = [];
            foreach ($documents as $doc) {
                $zaddArgs[(string) ($doc['recorded_at'] instanceof Carbon
                    ? $doc['recorded_at']->valueOf()
                    : Carbon::parse($doc['recorded_at'])->valueOf()
                )] = "{$doc['latitude']},{$doc['longitude']}";
            }
            if ($zaddArgs) {
                Redis::zadd($redisKey, $zaddArgs);
                Redis::expire($redisKey, 172800);
            }
        } catch (\Throwable $e) {
            \Log::warning('Redis batch write failed: ' . $e->getMessage());
        }

        // Update MySQL FIRST so the live-map dot is current even if MongoDB fails.
        if ($lastLat !== null) {
            $user->update([
                'last_latitude' => $lastLat,
                'last_longitude' => $lastLng,
                'last_location_at' => $now,
            ]);
        }

        try {
            LocationPing::insert($documents);
        } catch (\Throwable $e) {
            \Log::warning('LocationPing batch insert failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'count' => count($documents),
            ]);
        }

        return count($documents);
    }

    public function movementLog(int $companyId, int $userId, Carbon $date, int $limit = 100): array
    {
        return LocationPing::where('company_id', $companyId)
            ->where('user_id', $userId)
            ->forDate($date)
            ->orderByDesc('recorded_at')
            ->take($limit)
            ->get()
            ->map(fn ($p) => [
                'lat' => (float) $p->latitude,
                'lng' => (float) $p->longitude,
                'speed' => $p->speed ? (float) $p->speed : null,
                'accuracy' => $p->accuracy ? (float) $p->accuracy : null,
                'source' => $p->source,
                'is_background' => (bool) $p->is_background,
                'recorded_at' => $p->recorded_at->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    public function todayAttendanceSummary(int $companyId): Collection
    {
        return MrAttendance::where('company_id', $companyId)
            ->where('work_date', today())
            ->with('user:id,name,phone,last_location_at,tracking_active')
            ->orderBy('clock_in_at')
            ->get();
    }

    protected function resolveStatusLabel(User $user, ?MrAttendance $attendance, ?Visit $activeVisit, bool $isLive): string
    {
        if ($activeVisit) {
            return 'On visit';
        }
        if ($attendance?->isActive() && $isLive) {
            return 'In field';
        }
        if ($attendance?->isActive()) {
            return 'Clocked in';
        }
        if ($attendance?->clock_out_at) {
            return 'Day complete';
        }

        return 'Offline';
    }

    protected function resolveStatusColor(User $user, ?MrAttendance $attendance, ?Visit $activeVisit, bool $isLive): string
    {
        if ($activeVisit) {
            return 'emerald';
        }
        if ($attendance?->isActive() && $isLive) {
            return 'teal';
        }
        if ($attendance?->isActive()) {
            return 'amber';
        }
        if ($attendance?->clock_out_at) {
            return 'slate';
        }

        return 'red';
    }
}
