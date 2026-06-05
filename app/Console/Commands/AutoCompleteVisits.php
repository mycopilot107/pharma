<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Enums\VisitStatus;
use App\Models\DailyRoute;
use App\Models\MrAttendance;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class AutoCompleteVisits extends Command
{
    protected $signature = 'visits:auto-complete';

    protected $description = 'Auto-complete visits and attendance records left open at end of day';

    public function handle(): int
    {
        $now = now();

        $visitCount = $this->closeOpenVisits($now);
        $attendanceCount = $this->closeOpenAttendances($now);
        $routeCount = $this->closeOpenRoutes($now);
        $this->resetTrackingActiveFlags();
        $loggedOut = $this->logoutAllRepresentatives();

        $this->info("Auto-completed: {$visitCount} visit(s), {$attendanceCount} attendance(s), {$routeCount} route(s). Logged out {$loggedOut} representative(s).");

        return self::SUCCESS;
    }

    private function closeOpenVisits(Carbon $now): int
    {
        $visits = Visit::where('status', VisitStatus::InProgress)->get();

        foreach ($visits as $visit) {
            // Close at end of the day the rep checked in, so duration is realistic
            $checkoutAt = $visit->checked_in_at
                ? $visit->checked_in_at->copy()->endOfDay()
                : $now;

            $duration = $visit->checked_in_at
                ? (int) $visit->checked_in_at->diffInMinutes($checkoutAt)
                : null;

            $visit->update([
                'status' => VisitStatus::Completed,
                'checked_out_at' => $checkoutAt,
                'duration_minutes' => $duration,
            ]);
        }

        return $visits->count();
    }

    private function closeOpenAttendances(Carbon $now): int
    {
        $attendances = MrAttendance::where('status', MrAttendance::STATUS_ACTIVE)->get();

        foreach ($attendances as $attendance) {
            // Clock out at end of their work_date so the hours shown in reports are sensible
            $clockOutAt = Carbon::parse($attendance->work_date)->endOfDay();

            $attendance->update([
                'status' => MrAttendance::STATUS_COMPLETED,
                'clock_out_at' => $clockOutAt,
            ]);
        }

        return $attendances->count();
    }

    private function closeOpenRoutes(Carbon $now): int
    {
        return DailyRoute::where('status', 'in_progress')
            ->whereDate('route_date', '<', $now->toDateString())
            ->update(['status' => 'completed', 'ended_at' => $now]);
    }

    private function resetTrackingActiveFlags(): void
    {
        User::where('tracking_active', true)->update(['tracking_active' => false]);
    }

    private function logoutAllRepresentatives(): int
    {
        $representativeIds = User::where('role', UserRole::Representative)
            ->pluck('id');

        return PersonalAccessToken::whereIn('tokenable_id', $representativeIds)
            ->where('tokenable_type', User::class)
            ->delete();
    }
}
