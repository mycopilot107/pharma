<?php

namespace App\Http\Controllers\Mr;

use App\Enums\VisitStatus;
use App\Enums\VisitType;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DailyRoute;
use App\Models\Visit;
use App\Models\VisitPhoto;
use App\Services\AiReportingService;
use App\Services\TrackingService;
use App\Services\VisitValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VisitController extends Controller
{
    public function __construct(
        protected AiReportingService $aiReporting,
        protected TrackingService $tracking,
    ) {}

    public function index(Request $request)
    {
        $visits = Visit::where('user_id', Auth::id())
            ->with(['customer', 'dailyRoute'])
            ->when($request->date, fn ($q, $date) => $q->whereDate('created_at', $date))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('mr.visits.index', compact('visits'));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->activeVisit()) {
            return redirect()->route('mr.visits.show', $user->activeVisit())
                ->with('error', 'Complete your current visit before starting a new one.');
        }

        $customers = Customer::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $todayRoute = DailyRoute::where('user_id', $user->id)
            ->whereDate('route_date', now()->toDateString())
            ->first();

        return view('mr.visits.create', [
            'customers' => $customers,
            'todayRoute' => $todayRoute,
            'visitTypes' => VisitType::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->activeVisit()) {
            return back()->with('error', 'You already have an active visit.');
        }

        $validated = $request->validate([
            'visit_type' => ['required', Rule::enum(VisitType::class)],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'place_name' => ['required_without:customer_id', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'daily_route_id' => ['nullable', 'exists:daily_routes,id'],
            'start_now' => ['boolean'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $customer = null;
        if (! empty($validated['customer_id'])) {
            $customer = Customer::where('company_id', $user->company_id)
                ->findOrFail($validated['customer_id']);
        }

        if (! empty($validated['daily_route_id'])) {
            DailyRoute::where('id', $validated['daily_route_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        $visit = Visit::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'daily_route_id' => $validated['daily_route_id'] ?? null,
            'customer_id' => $customer?->id,
            'visit_type' => $validated['visit_type'],
            'place_name' => $customer?->name ?? $validated['place_name'],
            'address' => $customer?->address ?? ($validated['address'] ?? null),
            'notes' => $validated['notes'] ?? null,
            'status' => $request->boolean('start_now') ? VisitStatus::InProgress : VisitStatus::Planned,
            'planned_at' => now(),
            'checked_in_at' => $request->boolean('start_now') ? now() : null,
            'check_in_latitude' => $request->boolean('start_now') ? ($validated['latitude'] ?? null) : null,
            'check_in_longitude' => $request->boolean('start_now') ? ($validated['longitude'] ?? null) : null,
        ]);

        if ($visit->isInProgress() && $visit->daily_route_id) {
            DailyRoute::where('id', $visit->daily_route_id)
                ->whereNull('started_at')
                ->update(['started_at' => now(), 'status' => 'in_progress']);
        }

        return redirect()->route('mr.visits.show', $visit)
            ->with('success', $visit->isInProgress() ? 'Checked in successfully.' : 'Visit planned.');
    }

    public function show(Visit $visit)
    {
        $this->authorizeVisit($visit);

        $visit->load(['customer', 'photos', 'dailyRoute']);

        return view('mr.visits.show', [
            'visit' => $visit,
            'aiAvailable' => $this->aiReporting->isAvailable(),
        ]);
    }

    public function checkIn(Request $request, Visit $visit)
    {
        $this->authorizeVisit($visit);

        if ($visit->status !== VisitStatus::Planned) {
            return back()->with('error', 'This visit cannot be checked in.');
        }

        if (Auth::user()->activeVisit() && Auth::user()->activeVisit()->id !== $visit->id) {
            return back()->with('error', 'Complete your current visit first.');
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $visit->update([
            'status' => VisitStatus::InProgress,
            'checked_in_at' => now(),
            'check_in_latitude' => $validated['latitude'],
            'check_in_longitude' => $validated['longitude'],
        ]);

        if (Auth::user()->tracking_active) {
            $this->tracking->recordPing(
                Auth::user(),
                $validated['latitude'],
                $validated['longitude'],
                source: 'visit_checkin',
                dailyRouteId: $visit->daily_route_id,
            );
        }

        return back()->with('success', 'Checked in at '.now()->format('h:i A'));
    }

    public function checkOut(Request $request, Visit $visit)
    {
        $this->authorizeVisit($visit);

        if ($visit->status !== VisitStatus::InProgress) {
            return back()->with('error', 'This visit is not in progress.');
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $checkedOut = now();
        $duration = $visit->checked_in_at
            ? (int) $visit->checked_in_at->diffInMinutes($checkedOut)
            : null;

        $visit->update([
            'status' => VisitStatus::Completed,
            'checked_out_at' => $checkedOut,
            'check_out_latitude' => $validated['latitude'],
            'check_out_longitude' => $validated['longitude'],
            'duration_minutes' => $duration,
            'notes' => $validated['notes'] ?? $visit->notes,
        ]);

        if (Auth::user()->tracking_active) {
            $this->tracking->recordPing(
                Auth::user(),
                $validated['latitude'],
                $validated['longitude'],
                source: 'visit_checkout',
                dailyRouteId: $visit->daily_route_id,
            );
        }

        $visit->refresh();
        app(VisitValidationService::class)->validateAndStore($visit);
        $this->aiReporting->tryAutoSummarizeVisit($visit);

        return redirect()->route('mr.dashboard')
            ->with('success', 'Visit completed'.($duration ? " ({$duration} min)" : '').'.');
    }

    public function updateNotes(Request $request, Visit $visit)
    {
        $this->authorizeVisit($visit);

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:5000'],
        ]);

        $visit->update(['notes' => $validated['notes']]);

        return back()->with('success', 'Notes saved.');
    }

    public function uploadPhotos(Request $request, Visit $visit)
    {
        $this->authorizeVisit($visit);

        $request->validate([
            'photos' => ['required', 'array', 'max:5'],
            'photos.*' => ['image', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('visit-photos/'.$visit->id, 'public');

            VisitPhoto::create([
                'visit_id' => $visit->id,
                'path' => $path,
                'original_name' => $photo->getClientOriginalName(),
                'caption' => $request->input('caption'),
            ]);
        }

        return back()->with('success', 'Photo(s) uploaded.');
    }

    public function generateSummary(Visit $visit)
    {
        $this->authorizeVisit($visit);

        if ($visit->status !== VisitStatus::Completed) {
            return back()->with('error', 'Complete the visit before generating an AI summary.');
        }

        if (! $this->aiReporting->isAvailable()) {
            return back()->with('error', 'AI is not configured. Contact your administrator.');
        }

        try {
            $this->aiReporting->summarizeVisit($visit, Auth::id());
        } catch (\Throwable $e) {
            return back()->with('error', 'Summary failed: '.$e->getMessage());
        }

        return back()->with('success', 'AI visit summary generated.');
    }

    protected function authorizeVisit(Visit $visit): void
    {
        if ($visit->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
