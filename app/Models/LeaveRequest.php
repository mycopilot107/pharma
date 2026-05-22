<?php

namespace App\Models;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'days_count',
        'is_half_day',
        'half_day_period',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'manager_notes',
    ];

    protected function casts(): array
    {
        return [
            'leave_type' => LeaveType::class,
            'status' => LeaveStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'days_count' => 'decimal:1',
            'is_half_day' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === LeaveStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === LeaveStatus::Approved;
    }

    public function dateRangeLabel(): string
    {
        if ($this->start_date->equalTo($this->end_date)) {
            return $this->start_date->format('d M Y');
        }

        return $this->start_date->format('d M').' – '.$this->end_date->format('d M Y');
    }

    public function coversDate(Carbon $date): bool
    {
        if ($this->status !== LeaveStatus::Approved) {
            return false;
        }

        return $date->between($this->start_date, $this->end_date);
    }
}
