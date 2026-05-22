<?php

namespace App\Http\Controllers\Mr;

use App\Http\Controllers\Controller;
use App\Models\Target;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TargetController extends Controller
{
    public function index(Request $request)
    {
        $targets = Target::where('user_id', Auth::id())
            ->where('status', '!=', Target::STATUS_CANCELLED)
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $activeCount = Target::where('user_id', Auth::id())
            ->where('status', Target::STATUS_ACTIVE)
            ->count();

        return view('mr.targets.index', compact('targets', 'activeCount'));
    }

    public function show(Target $target)
    {
        if ($target->user_id !== Auth::id()) {
            abort(403);
        }

        $target->load('assigner');

        return view('mr.targets.show', compact('target'));
    }
}
