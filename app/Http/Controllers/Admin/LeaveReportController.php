<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeaveType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LeaveReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveReportController extends Controller
{
    public function __construct(protected LeaveReportService $reports) {}

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $filters = $request->only(['user_id', 'type', 'status', 'date_from', 'date_to']);
        $year = (int) ($request->input('year') ?: now()->year);

        $representatives = User::where('company_id', $companyId)
            ->where('role', 'representative')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.leaves.reports', [
            'summary' => $this->reports->summary($companyId, $filters),
            'byRepresentative' => $this->reports->byRepresentative($companyId, $filters),
            'byLeaveType' => $this->reports->byLeaveType($companyId, $filters),
            'balances' => $this->reports->balancesForCompany($companyId, $year),
            'representatives' => $representatives,
            'leaveTypes' => LeaveType::cases(),
            'filters' => $filters,
            'year' => $year,
        ]);
    }
}
