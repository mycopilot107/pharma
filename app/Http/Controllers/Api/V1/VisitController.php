<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\VisitStatus;
use App\Enums\VisitType;
use App\Http\Controllers\Controller;
use App\Http\Resources\VisitResource;
use App\Models\Customer;
use App\Models\DailyRoute;
use App\Models\Visit;
use App\Models\VisitPhoto;
use App\Services\AiReportingService;
use App\Services\TrackingService;
use App\Services\VisitValidationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VisitController extends Controller
{
    public function __construct(
        protected AiReportingService $aiReporting,
        protected TrackingService $tracking,
    ) {}

    public function index(Request $request)
    {
        $visits = Visit::where('user_id', $request->user()->id)
            ->with(['customer', 'photos'])
            ->when($request->date, fn ($q, $date) => $q->whereDate('created_at', $date))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        return VisitResource::collection($visits);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->activeVisit()) {
            return response()->json(['message' => 'You already have an active visit.'], 422);
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

        return (new VisitResource($visit->load(['customer', 'photos'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Visit $visit)
    {
        $this->authorizeVisit($visit, $request);

        return new VisitResource($visit->load(['customer', 'photos', 'dailyRoute']));
    }

    public function checkIn(Request $request, Visit $visit)
    {
        $this->authorizeVisit($visit, $request);
        $user = $request->user();

        if ($visit->status !== VisitStatus::Planned) {
            return response()->json(['message' => 'This visit cannot be checked in.'], 422);
        }

        if ($user->activeVisit() && $user->activeVisit()->id !== $visit->id) {
            return response()->json(['message' => 'Complete your current visit first.'], 422);
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

        if ($user->tracking_active) {
            $this->tracking->recordPing(
                $user,
                $validated['latitude'],
                $validated['longitude'],
                source: 'visit_checkin',
                dailyRouteId: $visit->daily_route_id,
            );
        }

        return new VisitResource($visit->fresh()->load(['customer', 'photos']));
    }

    public function checkOut(Request $request, Visit $visit)
    {
        $this->authorizeVisit($visit, $request);
        $user = $request->user();

        if ($visit->status !== VisitStatus::InProgress) {
            return response()->json(['message' => 'This visit is not in progress.'], 422);
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

        if ($user->tracking_active) {
            $this->tracking->recordPing(
                $user,
                $validated['latitude'],
                $validated['longitude'],
                source: 'visit_checkout',
                dailyRouteId: $visit->daily_route_id,
            );
        }

        $visit->refresh();
        app(VisitValidationService::class)->validateAndStore($visit);
        $this->aiReporting->tryAutoSummarizeVisit($visit);

        return new VisitResource($visit->load(['customer', 'photos', 'validation']));
    }

    public function updateNotes(Request $request, Visit $visit)
    {
        $this->authorizeVisit($visit, $request);

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:5000'],
        ]);

        $visit->update(['notes' => $validated['notes']]);

        return new VisitResource($visit);
    }

    public function uploadPhotos(Request $request, Visit $visit)
    {
        $this->authorizeVisit($visit, $request);

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

        return new VisitResource($visit->fresh()->load(['customer', 'photos']));
    }

    protected function authorizeVisit(Visit $visit, Request $request): void
    {
        if ($visit->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
