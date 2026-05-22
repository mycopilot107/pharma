<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TargetResource;
use App\Models\Target;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function index(Request $request)
    {
        $targets = Target::where('user_id', $request->user()->id)
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        return TargetResource::collection($targets);
    }

    public function show(Request $request, Target $target)
    {
        if ($target->user_id !== $request->user()->id) {
            abort(403);
        }

        return new TargetResource($target);
    }
}
