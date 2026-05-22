<?php

namespace App\Http\Controllers\Mr;

use App\Http\Controllers\Controller;
use App\Models\DailyRoute;
use App\Models\MrAttendance;
use App\Services\LeaveService;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    public function __construct(
        protected TrackingService $tracking,
        protected LeaveService $leaves,
    ) {}

    public function ping(Request $request)
    {
        if (! Auth::user()->tracking_active) {
            return response()->json(['ok' => false, 'message' => 'Not on duty'], 422);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
        ]);

        $routeId = DailyRoute::where('user_id', Auth::id())
            ->whereDate('route_date', today())
            ->whereIn('status', ['planned', 'in_progress'])
            ->value('id');

        $this->tracking->recordPing(
            Auth::user(),
            $validated['latitude'],
            $validated['longitude'],
            $validated['accuracy'] ?? null,
            'ping',
            $routeId,
        );

        return response()->json(['ok' => true, 'at' => now()->toIso8601String()]);
    }

    public function clockIn(Request $request)
    {
        if ($this->leaves->isOnApprovedLeave(Auth::id())) {
            return back()->with('error', 'You are on approved leave today and cannot clock in.');
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $attendance = $this->tracking->clockIn(
            Auth::user(),
            $validated['latitude'],
            $validated['longitude'],
        );

        return back()->with('success', 'Clocked in at '.$attendance->clock_in_at->format('h:i A').'. Live tracking is on.');
    }

    public function clockOut(Request $request)
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $attendance = $this->tracking->clockOut(
            Auth::user(),
            $validated['latitude'],
            $validated['longitude'],
        );

        return back()->with('success', 'Clocked out at '.$attendance->clock_out_at->format('h:i A').'. See you tomorrow!');
    }

    public function status()
    {
        $user = Auth::user();
        $attendance = MrAttendance::where('user_id', $user->id)
            ->where('work_date', today())
            ->first();

        return response()->json([
            'tracking_active' => (bool) $user->tracking_active,
            'attendance_active' => $attendance?->isActive() ?? false,
            'clock_in' => $attendance?->clock_in_at?->toIso8601String(),
        ]);
    }
}
