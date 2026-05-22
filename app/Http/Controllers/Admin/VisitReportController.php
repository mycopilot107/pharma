<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitReportController extends Controller
{
    public function index(Request $request)
    {
        $company = Auth::user()->company;

        $visits = Visit::where('company_id', $company->id)
            ->with(['user', 'customer', 'photos'])
            ->when($request->user_id, fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->visit_type, fn ($q, $type) => $q->where('visit_type', $type))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->date_from, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->date_to, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $representatives = User::where('company_id', $company->id)
            ->where('role', 'representative')
            ->orderBy('name')
            ->get();

        $summary = [
            'today' => Visit::where('company_id', $company->id)->whereDate('created_at', today())->count(),
            'completed_today' => Visit::where('company_id', $company->id)->whereDate('created_at', today())->where('status', 'completed')->count(),
            'in_progress' => Visit::where('company_id', $company->id)->where('status', 'in_progress')->count(),
        ];

        return view('admin.visits.index', compact('visits', 'representatives', 'summary'));
    }

    public function show(Visit $visit)
    {
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $visit->load(['user', 'customer', 'photos', 'dailyRoute']);

        return view('admin.visits.show', compact('visit'));
    }
}
