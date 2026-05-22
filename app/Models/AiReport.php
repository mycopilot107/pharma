<?php

namespace App\Models;

use App\Enums\AiReportType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiReport extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'generated_by',
        'type',
        'title',
        'report_date',
        'context_snapshot',
        'detected_insights',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'type' => AiReportType::class,
            'report_date' => 'date',
            'context_snapshot' => 'array',
            'detected_insights' => 'array',
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

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
