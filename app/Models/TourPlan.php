<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourPlan extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'week_start', 'status',
        'rejection_reason', 'notes', 'submitted_at', 'approved_at', 'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'week_start'   => 'date',
            'submitted_at' => 'datetime',
            'approved_at'  => 'datetime',
        ];
    }

    public function company(): BelongsTo  { return $this->belongsTo(Company::class); }
    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function stops(): HasMany
    {
        return $this->hasMany(TourPlanStop::class)
            ->orderBy('day_of_week')
            ->orderBy('sort_order');
    }

    public function isDraft(): bool     { return $this->status === 'draft'; }
    public function isSubmitted(): bool { return $this->status === 'submitted'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }

    public function weekLabel(): string
    {
        return $this->week_start->format('d M') . ' – ' . $this->week_start->copy()->addDays(5)->format('d M Y');
    }

    /** Returns the calendar date for a given day-of-week (1=Mon, 6=Sat). */
    public function dayDate(int $dayOfWeek): Carbon
    {
        return $this->week_start->copy()->addDays($dayOfWeek - 1);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'submitted' => 'Pending Approval',
            'approved'  => 'Approved',
            'rejected'  => 'Rejected',
            default     => 'Draft',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'approved'  => 'bg-emerald-100 text-emerald-800',
            'submitted' => 'bg-blue-100 text-blue-800',
            'rejected'  => 'bg-red-100 text-red-800',
            default     => 'bg-slate-100 text-slate-700',
        };
    }
}
