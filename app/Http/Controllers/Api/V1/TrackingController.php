<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\DailyRoute;
use App\Models\MrAttendance;
use App\Services\GeofenceService;
use App\Services\LeaveService;
use App\Services\TrackingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function __construct(
        protected TrackingService $tracking,
        protected GeofenceService $geofence,
        protected LeaveService $leaves,
    ) {}

    public function ping(Request $request)
    {
        if (! $request->user()->tracking_active) {
            return response()->json(['ok' => false, 'message' => 'Not on duty'], 422);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
            'altitude' => ['nullable', 'numeric'],
            'battery_percent' => ['nullable', 'integer', 'between:0,100'],
            'is_background' => ['boolean'],
        ]);

        $routeId = DailyRoute::where('user_id', $request->user()->id)
            ->whereDate('route_date', today())
            ->whereIn('status', ['planned', 'in_progress'])
            ->value('id');

        $ping = $this->tracking->recordPing(
            $request->user(),
            $validated['latitude'],
            $validated['longitude'],
            $validated['accuracy'] ?? null,
            'ping',
            $routeId,
            $validated['speed'] ?? null,
            $validated['heading'] ?? null,
            $validated['altitude'] ?? null,
            $validated['battery_percent'] ?? null,
            $request->boolean('is_background'),
        );

        return response()->json([
            'ok' => true,
            'at' => $ping->recorded_at->toIso8601String(),
            'ping_interval' => config('tracking.ping_interval_seconds'),
        ]);
    }

    public function pingBatch(Request $request)
    {
        if (! $request->user()->tracking_active) {
            return response()->json(['ok' => false, 'message' => 'Not on duty'], 422);
        }

        $validated = $request->validate([
            'pings' => ['required', 'array', 'max:50'],
            'pings.*.latitude' => ['required', 'numeric'],
            'pings.*.longitude' => ['required', 'numeric'],
            'pings.*.accuracy' => ['nullable', 'numeric'],
            'pings.*.speed' => ['nullable', 'numeric'],
            'pings.*.heading' => ['nullable', 'numeric'],
            'pings.*.is_background' => ['boolean'],
        ]);

        $count = $this->tracking->recordPingBatch($request->user(), $validated['pings']);

        return response()->json(['ok' => true, 'stored' => $count]);
    }

    public function geofences(Request $request)
    {
        $zones = $this->geofence->zonesForUser($request->user());

        return response()->json([
            'zones' => $zones->map(fn ($c) => [
                'customer_id' => $c->id,
                'name' => $c->name,
                'type' => $c->type?->value,
                'latitude' => (float) $c->latitude,
                'longitude' => (float) $c->longitude,
                'radius_meters' => $c->geofence_radius_meters
                    ?? config('tracking.geofence_default_radius_meters'),
                'auto_checkin' => (bool) $c->geofence_auto_checkin,
                'address' => $c->address,
            ]),
        ]);
    }

    public function geofenceEvent(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'event_type' => ['required', 'in:enter,exit'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $result = $this->geofence->handleEvent(
            $request->user(),
            (int) $validated['customer_id'],
            $validated['event_type'],
            $validated['latitude'],
            $validated['longitude'],
        );

        return response()->json($result, ($result['ok'] ?? false) ? 200 : 422);
    }

    public function routeHistory(Request $request)
    {
        $date = $request->date
            ? Carbon::parse($request->date)
            : today();

        return response()->json(
            $this->tracking->routeHistory(
                $request->user()->company_id,
                $request->user()->id,
                $date,
            )
        );
    }

    public function movementLog(Request $request)
    {
        $date = $request->date
            ? Carbon::parse($request->date)
            : today();

        return response()->json([
            'logs' => $this->tracking->movementLog(
                $request->user()->company_id,
                $request->user()->id,
                $date,
                (int) $request->get('limit', 100),
            ),
        ]);
    }

    public function trackingConfig(Request $request)
    {
        return response()->json([
            'ping_interval_seconds' => config('tracking.ping_interval_seconds'),
            'background_ping_interval_seconds' => config('tracking.background_ping_interval_seconds'),
            'geofence_default_radius_meters' => config('tracking.geofence_default_radius_meters'),
            'google_maps_api_key' => google_maps_api_key(),
        ]);
    }

    public function clockIn(Request $request)
    {
        if ($this->leaves->isOnApprovedLeave($request->user()->id)) {
            return response()->json(['message' => 'You are on approved leave today.'], 422);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $attendance = $this->tracking->clockIn(
            $request->user(),
            $validated['latitude'],
            $validated['longitude'],
        );

        return response()->json([
            'message' => 'Clocked in successfully. Live tracking is on.',
            'attendance' => [
                'clock_in' => $attendance->clock_in_at->toIso8601String(),
            ],
            'user' => new UserResource($request->user()->fresh()),
            'tracking' => [
                'ping_interval_seconds' => config('tracking.ping_interval_seconds'),
                'background_ping_interval_seconds' => config('tracking.background_ping_interval_seconds'),
            ],
        ]);
    }

    public function clockOut(Request $request)
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $attendance = $this->tracking->clockOut(
            $request->user(),
            $validated['latitude'],
            $validated['longitude'],
        );

        return response()->json([
            'message' => 'Clocked out successfully.',
            'attendance' => [
                'clock_out' => $attendance->clock_out_at->toIso8601String(),
            ],
            'user' => new UserResource($request->user()->fresh()),
        ]);
    }

    public function status(Request $request)
    {
        $user = $request->user();
        $attendance = MrAttendance::where('user_id', $user->id)
            ->where('work_date', today())
            ->first();

        $lastPing = $user->last_location_at;

        return response()->json([
            'tracking_active' => (bool) $user->tracking_active,
            'attendance_active' => $attendance?->isActive() ?? false,
            'clock_in' => $attendance?->clock_in_at?->toIso8601String(),
            'clock_out' => $attendance?->clock_out_at?->toIso8601String(),
            'last_location_at' => $lastPing?->toIso8601String(),
            'latitude' => $user->last_latitude ? (float) $user->last_latitude : null,
            'longitude' => $user->last_longitude ? (float) $user->last_longitude : null,
            'ping_interval_seconds' => config('tracking.ping_interval_seconds'),
        ]);
    }
}
