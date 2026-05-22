<?php

namespace App\Http\Controllers\Mr;

use App\Http\Controllers\Controller;
use App\Models\DailyRoute;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyRouteController extends Controller
{
    public function __construct(protected TrackingService $tracking) {}
    public function index()
    {
        $routes = DailyRoute::where('user_id', Auth::id())
            ->withCount('visits')
            ->latest('route_date')
            ->paginate(15);

        return view('mr.routes.index', compact('routes'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        if (DailyRoute::where('user_id', $user->id)->whereDate('route_date', $today)->exists()) {
            return back()->with('error', 'You already have a route for today.');
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if (! $user->tracking_active && isset($validated['latitude'], $validated['longitude'])) {
            $this->tracking->clockIn($user, $validated['latitude'], $validated['longitude']);
            $user->refresh();
        }

        $route = DailyRoute::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'route_date' => $today,
            'title' => $validated['title'] ?? 'Route — '.now()->format('d M Y'),
            'notes' => $validated['notes'] ?? null,
            'status' => 'in_progress',
            'started_at' => now(),
            'start_latitude' => $validated['latitude'] ?? null,
            'start_longitude' => $validated['longitude'] ?? null,
        ]);

        if (isset($validated['latitude'], $validated['longitude'])) {
            $this->tracking->recordPing(
                $user,
                $validated['latitude'],
                $validated['longitude'],
                source: 'route_start',
                dailyRouteId: $route->id,
            );
        }

        return redirect()->route('mr.dashboard')
            ->with('success', 'Daily route created. Add visits to build your plan.');
    }
}
