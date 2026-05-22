<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VisitPhoto extends Model
{
    protected $fillable = [
        'visit_id',
        'path',
        'original_name',
        'caption',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
