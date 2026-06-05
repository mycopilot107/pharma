<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        // Serve through PHP route so no server symlink is required.
        // Path format: visit-photos/{visitId}/{filename}
        $segments = explode('/', ltrim($this->path, '/'));
        // $segments[0] = 'visit-photos', $segments[1] = visitId, $segments[2] = filename
        return rtrim(config('app.url'), '/') . '/visit-photos/' . implode('/', array_slice($segments, 1));
    }
}
